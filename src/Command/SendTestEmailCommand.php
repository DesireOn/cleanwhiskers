<?php

declare(strict_types=1);

namespace App\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;

#[AsCommand(name: 'app:mail:test', description: 'Send a test email to verify mailer configuration')]
final class SendTestEmailCommand extends Command
{
    public function __construct(
        private readonly MailerInterface $mailer,
        private readonly string $defaultFrom,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('to', InputArgument::REQUIRED, 'Recipient email address')
            ->addOption('from', null, InputOption::VALUE_REQUIRED, 'From email address (defaults to OUTREACH_CONTACT_EMAIL)')
            ->addOption('subject', null, InputOption::VALUE_REQUIRED, 'Subject line', 'CleanWhiskers test email')
            ->addOption('text', null, InputOption::VALUE_REQUIRED, 'Plain text body', 'This is a test email from CleanWhiskers staging.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $to = (string) $input->getArgument('to');
        $from = (string) ($input->getOption('from') ?? '');
        $subject = (string) $input->getOption('subject');
        $text = (string) $input->getOption('text');

        $fromAddress = $from !== '' ? $from : $this->defaultFrom;
        if ($fromAddress === '') {
            $output->writeln('<error>No from address provided and OUTREACH_CONTACT_EMAIL is empty.</error>');
            return Command::FAILURE;
        }

        try {
            $email = (new Email())
                ->from($fromAddress)
                ->to($to)
                ->subject($subject)
                ->text($text);

            $this->mailer->send($email);
            $output->writeln(sprintf('<info>Test email sent to %s from %s.</info>', $to, $fromAddress));
            return Command::SUCCESS;
        } catch (\Throwable $e) {
            $output->writeln('<error>Failed to send test email: ' . $e->getMessage() . '</error>');
            return Command::FAILURE;
        }
    }
}

