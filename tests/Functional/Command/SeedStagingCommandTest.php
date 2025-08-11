<?php

declare(strict_types=1);

namespace App\Tests\Functional\Command;

use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\HttpFoundation\Request;

final class SeedStagingCommandTest extends KernelTestCase
{
    private function initDbEnv(): void
    {
        self::ensureKernelShutdown();
        $db = __DIR__.'/../../../var/test.db';
        if (file_exists($db)) {
            unlink($db);
        }
        $dsn = 'sqlite:///'.$db;
        putenv('DATABASE_URL='.$dsn);
        $_ENV['DATABASE_URL'] = $dsn;
        $_SERVER['DATABASE_URL'] = $dsn;
    }

    public function testSeedsInStaging(): void
    {
        $this->initDbEnv();
        self::bootKernel(['environment' => 'staging']);
        $application = new Application(self::$kernel);
        $command = $application->find('app:seed-staging');
        $tester = new CommandTester($command);
        self::assertSame(0, $tester->execute([]));

        self::ensureKernelShutdown();
        self::bootKernel(['environment' => 'staging']);
        $slug = 'groomer-0-biz';

        $response = self::$kernel->handle(Request::create('/cities/sofia'));
        self::assertSame(200, $response->getStatusCode());

        $response = self::$kernel->handle(Request::create('/groomers/'.$slug));
        self::assertSame(200, $response->getStatusCode());

        $response = self::$kernel->handle(Request::create('/groomers/sofia/mobile-dog-grooming'));
        self::assertSame(200, $response->getStatusCode());
    }

    public function testRequiresForceOutsideStaging(): void
    {
        $this->initDbEnv();
        self::bootKernel(['environment' => 'dev']);
        $application = new Application(self::$kernel);
        $command = $application->find('app:seed-staging');

        $tester = new CommandTester($command);
        self::assertSame(Command::FAILURE, $tester->execute([]));

        $tester = new CommandTester($command);
        self::assertSame(Command::SUCCESS, $tester->execute(['--force' => true]));
    }
}
