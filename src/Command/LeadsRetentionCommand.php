<?php

declare(strict_types=1);

namespace App\Command;

use App\Entity\AuditLog;
use App\Entity\Lead;
use App\Repository\AuditLogRepository;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'app:leads:retention', description: 'Apply data retention for leads (purge and anonymize)')]
final class LeadsRetentionCommand extends Command
{
    private const BATCH_SIZE = 100;

    public function __construct(
        private readonly EntityManagerInterface $em,
        private readonly LeadRepository $leadRepository,
        private readonly LeadRecipientRepository $leadRecipientRepository,
        private readonly AuditLogRepository $auditLogRepository,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addOption('now', null, InputOption::VALUE_REQUIRED, 'Override current time (ISO 8601) for cutoff calculations');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $nowOption = $input->getOption('now');
        if (is_string($nowOption) && $nowOption !== '') {
            $parsed = DateTimeImmutable::createFromFormat(DATE_ATOM, $nowOption) ?: new DateTimeImmutable($nowOption);
            $now = $parsed ?: new DateTimeImmutable();
        } else {
            $now = new DateTimeImmutable();
        }
        $unclaimedCutoff = $now->sub(new DateInterval('P90D'));
        $claimedCutoff = $now->sub(new DateInterval('P1Y'));

        $deleted = $this->purgeUnclaimed($unclaimedCutoff, $output);
        $anonymized = $this->anonymizeClaimed($claimedCutoff, $output);

        $output->writeln(sprintf('<info>Retention complete.</info> Purged: %d, Anonymized: %d', $deleted, $anonymized));

        return Command::SUCCESS;
    }

    private function purgeUnclaimed(DateTimeImmutable $cutoff, OutputInterface $output): int
    {
        $totalDeleted = 0;

        do {
            /** @var array<int, Lead> $batch */
            $batch = $this->leadRepository->createQueryBuilder('l')
                ->where('l.status != :claimed')
                ->andWhere('l.createdAt < :cutoff')
                ->setParameter('claimed', Lead::STATUS_CLAIMED)
                ->setParameter('cutoff', $cutoff)
                ->orderBy('l.id', 'ASC')
                ->setMaxResults(self::BATCH_SIZE)
                ->getQuery()
                ->getResult();

            if (0 === count($batch)) {
                break;
            }

            foreach ($batch as $lead) {
                $leadId = $lead->getId();
                if (null === $leadId) {
                    // Should not happen for persisted rows, but skip defensively
                    continue;
                }

                // Remove associated recipients first to satisfy FK constraints
                $recipients = $this->leadRecipientRepository->findByLead($lead);
                $removedRecipients = 0;
                foreach ($recipients as $recipient) {
                    $this->em->remove($recipient);
                    $removedRecipients++;
                }

                // Log purge before removal
                $this->auditLogRepository->log(
                    event: 'retention_purge',
                    subjectType: AuditLog::SUBJECT_LEAD,
                    subjectId: (int) $leadId,
                    metadata: [
                        'reason' => 'unclaimed_older_than_90_days',
                        'removedRecipients' => $removedRecipients,
                        'createdAt' => $lead->getCreatedAt()->format(DATE_ATOM),
                        'status' => $lead->getStatus(),
                    ],
                );

                $this->em->remove($lead);
                $totalDeleted++;
            }

            $this->em->flush();
            $this->em->clear();

            $output->writeln(sprintf('<comment>Purge batch committed.</comment> Deleted so far: %d', $totalDeleted));
        } while (true);

        return $totalDeleted;
    }

    private function anonymizeClaimed(DateTimeImmutable $cutoff, OutputInterface $output): int
    {
        $totalAnonymized = 0;

        do {
            /** @var array<int, Lead> $batch */
            $batch = $this->leadRepository->createQueryBuilder('l')
                ->where('l.status = :claimed')
                ->andWhere('l.claimedAt IS NOT NULL')
                ->andWhere('l.claimedAt < :cutoff')
                ->andWhere('l.email NOT LIKE :anonEmail')
                ->setParameter('claimed', Lead::STATUS_CLAIMED)
                ->setParameter('cutoff', $cutoff)
                ->setParameter('anonEmail', 'anon+lead%@anon.local')
                ->orderBy('l.id', 'ASC')
                ->setMaxResults(self::BATCH_SIZE)
                ->getQuery()
                ->getResult();

            if (0 === count($batch)) {
                break;
            }

            foreach ($batch as $lead) {
                $leadId = $lead->getId();
                if (null === $leadId) {
                    continue;
                }

                $originalEmail = $lead->getEmail();
                $originalName = $lead->getFullName();

                // Replace PII with deterministic placeholders based on ID
                $lead->setFullName('Anonymous');
                $lead->setEmail(sprintf('anon+lead%d@anon.local', $leadId));
                $lead->setPhone(null);
                $lead->setPetType(null);
                $lead->setBreedSize(null);
                $lead->setConsentToShare(false);
                $lead->setOwnerTokenHash('');
                $lead->setOwnerTokenExpiresAt(null);
                $lead->setUpdatedAt(new DateTimeImmutable());

                $this->auditLogRepository->log(
                    event: 'retention_anonymize',
                    subjectType: AuditLog::SUBJECT_LEAD,
                    subjectId: (int) $leadId,
                    metadata: [
                        'reason' => 'claimed_older_than_1_year',
                        'claimedAt' => $lead->getClaimedAt()?->format(DATE_ATOM),
                        'emailBefore' => $originalEmail,
                        'nameBefore' => $originalName,
                    ],
                );

                $totalAnonymized++;
            }

            $this->em->flush();
            $this->em->clear();

            $output->writeln(sprintf('<comment>Anonymize batch committed.</comment> Anonymized so far: %d', $totalAnonymized));
        } while (true);

        return $totalAnonymized;
    }
}
