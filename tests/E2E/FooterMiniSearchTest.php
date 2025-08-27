<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\TestCase;
use Symfony\Component\Panther\PantherTestCase;

if (!class_exists(PantherTestCase::class)) {
    class FooterMiniSearchTest extends TestCase
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
use Facebook\WebDriver\WebDriverBy;

final class FooterMiniSearchTest extends PantherTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testFooterMiniSearchRedirectsToCityListing(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $client = self::createPantherClient();
        $client->request('GET', '/');

        self::assertSelectorExists('footer form.footer-search input[name="city"]');

        $client->executeScript(sprintf("document.getElementById('footer-city').value='%s';", $city->getSlug()));
        $client->waitForReload(function () use ($client) {
            $client->executeScript('document.getElementById("footer-search").submit();');
        });

        self::assertSame(
            '/groomers/'.$city->getSlug().'/'.Service::MOBILE_DOG_GROOMING,
            parse_url($client->getCurrentURL(), PHP_URL_PATH)
        );
    }

    public function testFooterCityAutocompleteShowsSuggestions(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $this->em->persist($city);
        $this->em->flush();

        $client = self::createPantherClient();
        $client->request('GET', '/');

        $input = $client->getWebDriver()->findElement(WebDriverBy::cssSelector('#footer-city'));
        $input->sendKeys('so');
        $client->waitFor('#city-list .city-card');
        self::assertSelectorExists('#city-list .city-card[role="option"]');
    }
}
