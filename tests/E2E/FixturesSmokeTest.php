<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Repository\GroomerProfileRepository;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Console\Input\ArrayInput;

class FixturesSmokeTest extends WebTestCase
{
    public function testFixturesLoadAndPagesRender(): void
    {
        $client = static::createClient();
        $application = new Application(self::$kernel);
        $application->setAutoExit(false);
        $application->run(new ArrayInput([
            'command' => 'doctrine:fixtures:load',
            '--no-interaction' => true,
        ]));
        $client->request('GET', '/groomers/sofia/'.\App\Entity\Service::MOBILE_DOG_GROOMING);
        self::assertResponseIsSuccessful();

        $groomerRepo = static::getContainer()->get(GroomerProfileRepository::class);
        $groomer = $groomerRepo->findOneBy([]);
        self::assertNotNull($groomer);

        $client->request('GET', '/groomers/'.$groomer->getSlug());
        self::assertResponseIsSuccessful();
    }
}
