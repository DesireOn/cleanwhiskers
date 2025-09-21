<?php

declare(strict_types=1);

namespace App\Tests\Command;

use App\Command\LeadsRetentionCommand;
use App\Entity\AuditLog;
use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service;
use App\Repository\AuditLogRepository;
use App\Repository\LeadRecipientRepository;
use App\Repository\LeadRepository;
use DateInterval;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Tester\CommandTester;

final class LeadsRetentionCommandTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private LeadRepository $leadRepository;
    private LeadRecipientRepository $recipientRepository;
    private AuditLogRepository $auditRepository;

    protected function setUp(): void
    {
        self::bootKernel();
        $container = static::getContainer();

        $this->em = $container->get('doctrine')->getManager();
        $this->leadRepository = $container->get(LeadRepository::class);
        $this->recipientRepository = $container->get(LeadRecipientRepository::class);
        $this->auditRepository = $container->get(AuditLogRepository::class);

        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testRetentionPurgesAndAnonymizes(): void
    {
        $now = new DateTimeImmutable('2025-01-01T00:00:00+00:00');

        // Seed required relations
        $city = new City('Sofia');
        $city->refreshSlugFrom('sofia');
        $service = (new Service())->setName('Mobile Dog Grooming');
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->flush();

        // Unclaimed lead older than 90 days
        $unclaimed = new Lead($city, $service, 'Old Unclaimed', 'old@example.com');
        $this->em->persist($unclaimed);
        $this->em->flush();

        $unclaimedId = (int) $unclaimed->getId();
        $oldCreatedAt = $now->sub(new DateInterval('P91D'));
        $this->em->getConnection()->executeStatement(
            'UPDATE lead_capture SET created_at = :dt WHERE id = :id',
            ['dt' => $oldCreatedAt->format('Y-m-d H:i:s'), 'id' => $unclaimedId]
        );

        // Add a recipient linked to the unclaimed lead (to be removed by purge)
        $recipient = new LeadRecipient($unclaimed, 'recipient@example.com', 'hash', $now->add(new DateInterval('P7D')));
        $this->em->persist($recipient);

        // Claimed lead older than 1 year -> should be anonymized
        $claimed = new Lead($city, $service, 'Old Claimed', 'claimed@example.com');
        $claimed->setStatus(Lead::STATUS_CLAIMED);
        $claimed->setClaimedAt($now->sub(new DateInterval('P370D')));
        $this->em->persist($claimed);

        $this->em->flush();
        $claimedId = (int) $claimed->getId();

        // Run command with frozen time
        $command = static::getContainer()->get(LeadsRetentionCommand::class);
        $tester = new CommandTester($command);
        $status = $tester->execute(['--now' => $now->format(DATE_ATOM)]);

        self::assertSame(LeadsRetentionCommand::SUCCESS, $status);

        // Purge assertions
        self::assertNull($this->leadRepository->find($unclaimedId), 'Unclaimed old lead should be deleted');
        self::assertSame(0, $this->recipientRepository->count([]), 'Recipients linked to purged leads should be removed');

        // Anonymize assertions
        $reloadedClaimed = $this->leadRepository->find($claimedId);
        self::assertNotNull($reloadedClaimed);
        self::assertSame('Anonymous', $reloadedClaimed->getFullName());
        self::assertSame(sprintf('anon+lead%d@anon.local', $claimedId), $reloadedClaimed->getEmail());
        self::assertNull($reloadedClaimed->getPhone());

        // Audit logs present
        $purgeLogs = $this->auditRepository->findBy(['event' => 'retention_purge', 'subjectType' => AuditLog::SUBJECT_LEAD, 'subjectId' => $unclaimedId]);
        $anonLogs = $this->auditRepository->findBy(['event' => 'retention_anonymize', 'subjectType' => AuditLog::SUBJECT_LEAD, 'subjectId' => $claimedId]);
        self::assertNotEmpty($purgeLogs, 'Should log retention_purge');
        self::assertNotEmpty($anonLogs, 'Should log retention_anonymize');
    }
}

