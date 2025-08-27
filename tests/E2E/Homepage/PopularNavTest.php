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
        $mobile = (new Service())->setName('Mobile Dog Grooming');
        $mobile->refreshSlugFrom($mobile->getName());
        $this->em->persist($mobile);

        foreach (['Bucharest', 'Ruse', 'Sofia'] as $name) {
            $city = new City($name);
            $this->em->persist($city);

            $groomer = new \App\Entity\GroomerProfile(null, $city, $name.' Groomer', 'About');
            $groomer->getServices()->add($mobile);
            $this->em->persist($groomer);
        }

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
    }
}
