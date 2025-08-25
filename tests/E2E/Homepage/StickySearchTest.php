<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

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

use App\Entity\City;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Facebook\WebDriver\WebDriverBy;

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

    public function testStickySearchCityAutocompleteFiltersAndSelects(): void
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

        self::assertSelectorExists('#sticky-city[role="combobox"][aria-controls="city-list"]');
        self::assertSelectorExists('#city-list[role="listbox"]');
        $hidden = $client->executeScript('return document.getElementById("city-list").hidden;');
        self::assertTrue($hidden);

        $input = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('#sticky-city'));
        $input->sendKeys('va');
        $client->waitFor('#city-list .city-card');
        $visible = $client->executeScript('return !document.getElementById("city-list").hidden;');
        self::assertTrue($visible);
        $count = $client->executeScript('return document.querySelectorAll("#city-list .city-card").length;');
        self::assertSame(1, $count);
        $minHeight = $client->executeScript('return parseFloat(getComputedStyle(document.querySelector("#city-list .city-card")).minHeight);');
        self::assertGreaterThanOrEqual(44, $minHeight);
        $role = $client->executeScript('return document.querySelector("#city-list .city-card").getAttribute("role");');
        self::assertSame('option', $role);

        $input->clear();
        $input->sendKeys('so');
        $client->waitFor('#city-list .city-card');
        $client->getWebDriver()->findElement(WebDriverBy::cssSelector('#city-list .city-card'))->click();
        $hidden = $client->executeScript('return document.getElementById("city-list").hidden;');
        self::assertTrue($hidden);
        $selected = $input->getAttribute('value');
        self::assertSame($city->getSlug(), $selected);
    }

    public function testStickySearchFormSubmits(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $client = self::createPantherClient();
        $client->request('GET', '/');

        $input = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('#sticky-city'));
        $input->sendKeys('so');
        $client->waitFor('#city-list .city-card');
        $visible = $client->executeScript('return !document.getElementById("city-list").hidden;');
        self::assertTrue($visible);
        $client->getWebDriver()->findElement(WebDriverBy::cssSelector('#city-list .city-card'))->click();
        $hidden = $client->executeScript('return document.getElementById("city-list").hidden;');
        self::assertTrue($hidden);

        $client->waitForReload(function () use ($client) {
            $client->executeScript('document.getElementById("sticky-search-form").submit();');
        });

        self::assertSame('/groomers/'.$city->getSlug(), parse_url($client->getCurrentURL(), PHP_URL_PATH));
    }
}
