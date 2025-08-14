<?php

declare(strict_types=1);

namespace App\Tests\E2E\Homepage;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;

final class FeaturedCarouselTest extends WebTestCase
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

    public function testCarouselButtonsAndLinksWork(): void
    {
        $city = new City('Varna');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Grooming');
        $service->refreshSlugFrom($service->getName());
        $this->em->persist($city);
        $this->em->persist($service);

        for ($i = 0; $i < 2; ++$i) {
            $user = (new User())
                ->setEmail('g'.$i.'@example.com')
                ->setRoles([User::ROLE_GROOMER])
                ->setPassword('hash');
            $profile = new GroomerProfile($user, $city, 'Biz '.$i, 'Friendly cat specialist');
            $profile->refreshSlugFrom($profile->getBusinessName());
            $profile->addService($service);
            $this->em->persist($user);
            $this->em->persist($profile);
        }

        $author = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');
        $this->em->persist($author);
        $this->em->flush();

        $profiles = $this->em->getRepository(GroomerProfile::class)->findAll();
        foreach ($profiles as $profile) {
            $this->em->persist(new Review($profile, $author, 5, 'Great'));
        }
        $this->em->flush();

        $crawler = $this->client->request('GET', '/');
        self::assertResponseIsSuccessful();

        $section = $crawler->filter('#featured-groomers');
        self::assertSame(1, $section->count());
        self::assertSame('0', $section->filter('.carousel__track')->attr('tabindex'));
        self::assertSame(1, $section->filter('[data-carousel-prev]')->count());
        self::assertSame(1, $section->filter('[data-carousel-next]')->count());

        $cssPath = static::getContainer()->getParameter('kernel.project_dir').'/assets/styles/home.css';
        self::assertStringContainsString('scroll-snap-type: x mandatory', file_get_contents($cssPath));

        $hrefs = $section->filter('.carousel__card a')->each(fn (Crawler $link) => $link->attr('href'));
        foreach ($hrefs as $href) {
            $this->client->request('GET', $href);
            self::assertResponseIsSuccessful();
        }
    }
}
