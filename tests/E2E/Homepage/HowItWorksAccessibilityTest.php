<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class HowItWorksAccessibilityTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testCardsAreFocusableAndReducedMotionStylesPresent(): void
    {
        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $cards = $crawler->filter('#how-it-works .how-it-works__card');
        self::assertGreaterThanOrEqual(3, $cards->count());

        $cards->each(function (Crawler $card): void {
            self::assertSame('0', $card->attr('tabindex'));
        });

        $crawler->filter('#how-it-works img')->each(function (Crawler $img): void {
            self::assertSame('', $img->attr('alt'));
            self::assertSame('true', $img->attr('aria-hidden'));
        });

        $cssPath = static::getContainer()->getParameter('kernel.project_dir').'/assets/styles/home.css';
        self::assertStringContainsString('@media (prefers-reduced-motion: reduce)', file_get_contents($cssPath));
    }
}
