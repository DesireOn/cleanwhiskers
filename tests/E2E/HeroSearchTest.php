<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\TestCase;

if (!class_exists(PantherTestCase::class)) {
    class HeroSearchTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

use App\Entity\City;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Panther\PantherTestCase;

final class HeroSearchTest extends PantherTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testSelectingServiceRedirectsToListing(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Grooming');
        $service->refreshSlugFrom($service->getName());
        $otherCity = new City('Varna');
        $otherCity->refreshSlugFrom($otherCity->getName());

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($otherCity);
        $this->em->flush();

        $client = self::createPantherClient();
        $client->request('GET', '/');

        self::assertSelectorExists('#city[role="combobox"][aria-controls="city-list"]');

        $count = $client->executeScript('document.getElementById("city").value="va"; document.getElementById("city").dispatchEvent(new Event("input")); return document.querySelectorAll("#city-list [role=\\"option\\"]").length;');
        self::assertSame(1, $count);

        $client->executeScript(sprintf("document.querySelector('.hero__service[data-value=\"%s\"]').click();", $service->getSlug()));
        $client->executeScript(sprintf("document.getElementById('city').value='%s';", $city->getSlug()));

        $client->waitForReload(function () use ($client) {
            $client->executeScript('document.getElementById("search-form").submit();');
        });

        self::assertSame('/groomers/'.$city->getSlug().'/'.$service->getSlug(), parse_url($client->getCurrentURL(), PHP_URL_PATH));
    }
}
