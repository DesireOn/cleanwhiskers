<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service;
use App\Entity\Testimonial;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GroomerTestimonialsTest extends WebTestCase
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

    public function testListPrefersRealTestimonials(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $user = (new User())
            ->setEmail('g@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $groomer = new GroomerProfile($user, $city, 'Groomer', 'About');
        $groomer->refreshSlugFrom($groomer->getBusinessName());
        $groomer->addService($service);

        $real = new Testimonial('Real', 'City', 'Real quote');
        $placeholder = new Testimonial('Placeholder', 'City', 'Placeholder quote');
        $placeholder->markPlaceholder();
        $prop = new \ReflectionProperty(Testimonial::class, 'createdAt');
        $prop->setAccessible(true);
        $prop->setValue($placeholder, new \DateTimeImmutable());
        $prop->setValue($real, new \DateTimeImmutable('-1 day'));

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($user);
        $this->em->persist($groomer);
        $this->em->persist($real);
        $this->em->persist($placeholder);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Real quote', $content);
        self::assertStringNotContainsString('Placeholder quote', $content);
    }

    public function testListFallsBackToPlaceholder(): void
    {
        $city = new City('Plovdiv');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Trim');
        $service->refreshSlugFrom($service->getName());

        $user = (new User())
            ->setEmail('g2@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $groomer = new GroomerProfile($user, $city, 'Groomer2', 'About');
        $groomer->refreshSlugFrom($groomer->getBusinessName());
        $groomer->addService($service);

        $placeholder = new Testimonial('Placeholder', 'City', 'Placeholder quote');
        $placeholder->markPlaceholder();

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($user);
        $this->em->persist($groomer);
        $this->em->persist($placeholder);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Placeholder quote', $content);
    }
}
