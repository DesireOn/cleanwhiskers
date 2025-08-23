<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(PantherTestCase::class)) {
    class StickySearchTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

final class StickySearchTest extends PantherTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testStickySearchCompactsOnScroll(): void
    {
        $client = self::createPantherClient();
        $client->request('GET', '/');

        self::assertSelectorNotExists('#sticky-search.search--compact');

        $serviceHiddenInitial = $client->executeScript('return window.getComputedStyle(document.getElementById("sticky-service")).display === "none";');
        self::assertTrue($serviceHiddenInitial);

        $client->executeScript('window.scrollTo(0, 250); window.dispatchEvent(new Event("scroll"));');
        $client->waitFor('#sticky-search.search--compact');

        self::assertSelectorExists('#sticky-search.search--compact');

        $cityVisible = $client->executeScript('return window.getComputedStyle(document.getElementById("sticky-city")).display !== "none";');
        self::assertTrue($cityVisible);

        $serviceHidden = $client->executeScript('return window.getComputedStyle(document.getElementById("sticky-service")).display === "none";');
        self::assertTrue($serviceHidden);

        $client->executeScript('window.scrollTo(0, 0); window.dispatchEvent(new Event("scroll"));');
        $client->waitFor('#sticky-search:not(.search--compact)');

        self::assertSelectorNotExists('#sticky-search.search--compact');
    }

    public function testStickySearchAutocompleteFiltersCities(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $otherCity = new City('Varna');
        $otherCity->refreshSlugFrom($otherCity->getName());
        $this->em->persist($city);
        $this->em->persist($otherCity);
        $this->em->flush();

        $client = self::createPantherClient();
        $client->request('GET', '/');

        self::assertSelectorExists('#sticky-city[list="city-list"]');

        $count = $client->executeScript("document.getElementById('sticky-city').value='va'; document.getElementById('sticky-city').dispatchEvent(new Event('input')); return document.querySelectorAll('#city-list option').length;");
        self::assertSame(1, $count);
    }
}
