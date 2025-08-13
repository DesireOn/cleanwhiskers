<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FooterLinksRenderTest extends WebTestCase
{
    private EntityManagerInterface $em;
    private \Symfony\Bundle\FrameworkBundle\KernelBrowser $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testFooterRendersLimitedLinks(): void
    {
        for ($i = 1; $i <= 7; ++$i) {
            $city = new City('City '.$i);
            $city->refreshSlugFrom('City '.$i);
            $this->em->persist($city);
        }

        for ($i = 1; $i <= 7; ++$i) {
            $service = new Service();
            $service->setName('Service '.$i);
            $service->refreshSlugFrom('Service '.$i);
            $this->em->persist($service);
        }

        $this->em->flush();

        $crawler = $this->client->request('GET', '/');

        self::assertResponseIsSuccessful();
        self::assertSelectorExists('footer .footer-nav a[href="#about"]');
        self::assertSelectorExists('footer .footer-nav a[href="#contact"]');
        self::assertSelectorExists('footer .footer-nav a[href="#faq"]');
        self::assertSelectorExists('footer .footer-nav a[href="#blog"]');
        self::assertSelectorExists('footer .footer-nav a[href="#terms"]');
        self::assertSelectorExists('footer .footer-nav a[href="#privacy"]');

        self::assertSame(5, $crawler->filter('.footer-cities li')->count());
        self::assertSame(5, $crawler->filter('.footer-services li')->count());
    }
}
