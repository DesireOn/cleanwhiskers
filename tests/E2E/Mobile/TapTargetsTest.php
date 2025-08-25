<?php

declare(strict_types=1);

namespace App\Tests\E2E\Mobile;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(PantherTestCase::class)) {
    class TapTargetsTest extends TestCase
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
use Facebook\WebDriver\WebDriverDimension;

final class TapTargetsTest extends PantherTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testTapTargetsAreLargeEnough(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $client = self::createPantherClient();
        $client->manage()->window()->setSize(new WebDriverDimension(360, 640));
        $client->request('GET', '/');

        $client->executeScript('document.getElementById("nav-toggle").click();');

        $input = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('#city'));
        $input->sendKeys('so');
        $client->waitFor('#city-list .city-card');

        $btnHeight = $client->executeScript('return document.querySelector(".btn").getBoundingClientRect().height;');
        $navLinkHeight = $client->executeScript('return document.querySelector(".nav__link").getBoundingClientRect().height;');
        $cityCardHeight = $client->executeScript('return document.querySelector("#city-list .city-card").getBoundingClientRect().height;');

        self::assertGreaterThanOrEqual(44, $btnHeight);
        self::assertGreaterThanOrEqual(44, $navLinkHeight);
        self::assertGreaterThanOrEqual(44, $cityCardHeight);
    }
}
