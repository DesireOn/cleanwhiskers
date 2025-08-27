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

        $mobile = (new Service())->setName('Mobile Dog Grooming');
        $mobile->refreshSlugFrom($mobile->getName());
        $this->em->persist($mobile);

        $service = (new Service())->setName('Grooming');
        $service->refreshSlugFrom($service->getName());
        $this->em->persist($service);

        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $citySlugs = ['bucharest', 'ruse', 'sofia'];
        foreach ($citySlugs as $slug) {
            $link = sprintf(
                '.popular-cities__link[href="/groomers/%s/%s"]',
                $slug,
                Service::MOBILE_DOG_GROOMING
            );
            self::assertSame(1, $crawler->filter($link)->count());
        }

        foreach ($citySlugs as $slug) {
            $this->client->request('GET', '/groomers/'.$slug.'/'.Service::MOBILE_DOG_GROOMING);
            self::assertResponseIsSuccessful();
        }

        $firstCity = 'bucharest';
        $href = sprintf('/groomers/%s/grooming', $firstCity);
        self::assertSame(1, $crawler->filter(sprintf('#popular-services a[href="%s"]', $href))->count());

        $this->client->request('GET', $href);
        self::assertResponseIsSuccessful();

        $cssPath = static::getContainer()->getParameter('kernel.project_dir').'/public/css/sections/popular-services.css';
        $css = file_get_contents($cssPath);
        self::assertStringContainsString('.popular-services__spotlight:focus', $css);
    }
}
