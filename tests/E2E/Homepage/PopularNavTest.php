<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use App\Entity\City;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PopularNavTest extends WebTestCase
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

    public function testLinksResolveAndFocusStylesPresent(): void
    {
        foreach (['Bucharest', 'Ruse', 'Sofia'] as $name) {
            $city = new City($name);
            $this->em->persist($city);
        }

        foreach (['Dog', 'Cat', 'Mobile'] as $name) {
            $service = (new Service())->setName($name);
            $this->em->persist($service);
        }

        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $citySlugs = ['bucharest', 'ruse', 'sofia'];
        foreach ($citySlugs as $slug) {
            $link = sprintf('#popular a[href="/cities/%s"]', $slug);
            self::assertSame(1, $crawler->filter($link)->count());
        }

        foreach ($citySlugs as $slug) {
            $this->client->request('GET', '/cities/'.$slug);
            self::assertResponseIsSuccessful();
        }

        $firstCity = 'bucharest';
        $serviceSlugs = ['dog', 'cat', 'mobile'];
        foreach ($serviceSlugs as $serviceSlug) {
            $href = sprintf('/groomers/%s/%s', $firstCity, $serviceSlug);
            self::assertSame(1, $crawler->filter(sprintf('#popular a[href="%s"]', $href))->count());
        }

        foreach ($serviceSlugs as $serviceSlug) {
            $href = sprintf('/groomers/%s/%s', $firstCity, $serviceSlug);
            $this->client->request('GET', $href);
            self::assertResponseIsSuccessful();
        }

        $cssPath = static::getContainer()->getParameter('kernel.project_dir').'/assets/styles/home.css';
        $css = file_get_contents($cssPath);
        self::assertStringContainsString('.popular-card:focus', $css);
    }
}
