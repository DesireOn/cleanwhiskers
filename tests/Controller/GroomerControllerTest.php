<?php

declare(strict_types=1);

namespace App\Tests\Controller;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\Testimonial;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class GroomerControllerTest extends WebTestCase
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

    public function testProfileDisplaysReviews(): void
    {
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $city = new City('Sofia');
        $groomer = new GroomerProfile($groomerUser, $city, 'Best Groomers', 'About');
        $groomer->refreshSlugFrom($groomer->getBusinessName());

        $author = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');
        $review = new Review($groomer, $author, 5, 'Excellent service!');
        $review->markVerified();

        $this->em->persist($groomerUser);
        $this->em->persist($city);
        $this->em->persist($groomer);
        $this->em->persist($author);
        $this->em->persist($review);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$groomer->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Excellent service!', $content);
        self::assertStringContainsString('User '.$author->getId(), $content);
        self::assertStringContainsString('★', $content);
        self::assertStringContainsString('Verified', $content);
    }

    public function testListByCityServiceFiltersByRating(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $highUser = (new User())
            ->setEmail('high@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $high = new GroomerProfile($highUser, $city, 'High', 'About');
        $high->refreshSlugFrom($high->getBusinessName());
        $high->addService($service);

        $lowUser = (new User())
            ->setEmail('low@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $low = new GroomerProfile($lowUser, $city, 'Low', 'About');
        $low->refreshSlugFrom($low->getBusinessName());
        $low->addService($service);

        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($highUser);
        $this->em->persist($high);
        $this->em->persist($lowUser);
        $this->em->persist($low);
        $this->em->persist($author);
        $this->em->flush();

        $this->em->persist(new Review($high, $author, 5, 'Great'));
        $this->em->persist(new Review($low, $author, 3, 'Ok'));
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug().'?rating=4');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('High', $content);
        self::assertStringNotContainsString('Low', $content);
    }

    public function testListByCityServiceSortsRecommended(): void
    {
        $city = new City('Varna');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Trim');
        $service->refreshSlugFrom($service->getName());

        $verifiedUser = (new User())
            ->setEmail('verified@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $verifiedHigh = new GroomerProfile($verifiedUser, $city, 'Verified High', 'About');
        $verifiedHigh->refreshSlugFrom($verifiedHigh->getBusinessName());
        $verifiedHigh->addService($service);

        $unverifiedHigh = new GroomerProfile(null, $city, 'Unverified High', 'About');
        $unverifiedHigh->refreshSlugFrom($unverifiedHigh->getBusinessName());
        $unverifiedHigh->addService($service);

        $lowUser = (new User())
            ->setEmail('low@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $verifiedLow = new GroomerProfile($lowUser, $city, 'Verified Low', 'About');
        $verifiedLow->refreshSlugFrom($verifiedLow->getBusinessName());
        $verifiedLow->addService($service);

        $author = (new User())
            ->setEmail('author@example.com')
            ->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($verifiedUser);
        $this->em->persist($verifiedHigh);
        $this->em->persist($unverifiedHigh);
        $this->em->persist($lowUser);
        $this->em->persist($verifiedLow);
        $this->em->persist($author);
        $this->em->flush();

        $this->em->persist(new Review($verifiedHigh, $author, 5, 'Great'));
        $this->em->persist(new Review($unverifiedHigh, $author, 5, 'Great'));
        $this->em->persist(new Review($verifiedLow, $author, 3, 'Ok'));
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug().'?sort=recommended');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $posVerifiedHigh = strpos($content, 'Verified High');
        $posUnverifiedHigh = strpos($content, 'Unverified High');
        $posVerifiedLow = strpos($content, 'Verified Low');
        self::assertNotFalse($posVerifiedHigh);
        self::assertNotFalse($posUnverifiedHigh);
        self::assertNotFalse($posVerifiedLow);
        self::assertTrue($posVerifiedHigh < $posUnverifiedHigh);
        self::assertTrue($posUnverifiedHigh < $posVerifiedLow);
    }

    public function testListByCityServiceSortsByPriceAsc(): void
    {
        $city = new City('Plovdiv');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $cheapUser = (new User())
            ->setEmail('cheap@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $cheap = new GroomerProfile($cheapUser, $city, 'Cheap', 'About');
        $cheap->refreshSlugFrom($cheap->getBusinessName());
        $cheap->addService($service);
        $cheap->setPriceRange('10');

        $expensiveUser = (new User())
            ->setEmail('expensive@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $expensive = new GroomerProfile($expensiveUser, $city, 'Expensive', 'About');
        $expensive->refreshSlugFrom($expensive->getBusinessName());
        $expensive->addService($service);
        $expensive->setPriceRange('30');

        $noPriceUser = (new User())
            ->setEmail('noprice@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $noPrice = new GroomerProfile($noPriceUser, $city, 'No Price', 'About');
        $noPrice->refreshSlugFrom($noPrice->getBusinessName());
        $noPrice->addService($service);

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($cheapUser);
        $this->em->persist($cheap);
        $this->em->persist($expensiveUser);
        $this->em->persist($expensive);
        $this->em->persist($noPriceUser);
        $this->em->persist($noPrice);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug().'?sort=price_asc');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $posCheap = strpos($content, 'Cheap');
        $posExpensive = strpos($content, 'Expensive');
        $posNoPrice = strpos($content, 'No Price');
        self::assertNotFalse($posCheap);
        self::assertNotFalse($posExpensive);
        self::assertNotFalse($posNoPrice);
        self::assertTrue($posCheap < $posExpensive);
        self::assertTrue($posExpensive < $posNoPrice);
    }

    public function testListByCityServiceSortsByRatingDesc(): void
    {
        $city = new City('Ruse');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Clip');
        $service->refreshSlugFrom($service->getName());

        $highUser = (new User())
            ->setEmail('high@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $high = new GroomerProfile($highUser, $city, 'High', 'About');
        $high->refreshSlugFrom($high->getBusinessName());
        $high->addService($service);

        $midUser = (new User())
            ->setEmail('mid@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $mid = new GroomerProfile($midUser, $city, 'Mid', 'About');
        $mid->refreshSlugFrom($mid->getBusinessName());
        $mid->addService($service);

        $lowUser = (new User())
            ->setEmail('low@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $low = new GroomerProfile($lowUser, $city, 'Low', 'About');
        $low->refreshSlugFrom($low->getBusinessName());
        $low->addService($service);

        $author = (new User())
            ->setEmail('author3@example.com')
            ->setPassword('hash');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($highUser);
        $this->em->persist($high);
        $this->em->persist($midUser);
        $this->em->persist($mid);
        $this->em->persist($lowUser);
        $this->em->persist($low);
        $this->em->persist($author);
        $this->em->flush();

        $this->em->persist(new Review($high, $author, 5, 'Great'));
        $this->em->persist(new Review($mid, $author, 4, 'Good'));
        $this->em->persist(new Review($low, $author, 3, 'Ok'));
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug().'?sort=rating_desc');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        $posHigh = strpos($content, 'High');
        $posMid = strpos($content, 'Mid');
        $posLow = strpos($content, 'Low');
        self::assertNotFalse($posHigh);
        self::assertNotFalse($posMid);
        self::assertNotFalse($posLow);
        self::assertTrue($posHigh < $posMid);
        self::assertTrue($posMid < $posLow);
    }

    public function testListByCityServicePaginatesWithSorting(): void
    {
        $city = new City('Burgas');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $this->em->persist($city);
        $this->em->persist($service);

        for ($i = 1; $i <= 21; ++$i) {
            $user = (new User())
                ->setEmail(sprintf('p%d@example.com', $i))
                ->setRoles([User::ROLE_GROOMER])
                ->setPassword('hash');
            $profile = new GroomerProfile($user, $city, 'Groomer '.$i, 'About');
            $profile->refreshSlugFrom($profile->getBusinessName());
            $profile->addService($service);
            $profile->setPriceRange((string) $i);
            $this->em->persist($user);
            $this->em->persist($profile);
        }
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug().'?sort=price_asc&offset=20');
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Groomer 21', $content);
        self::assertStringNotContainsString('Groomer 1', $content);
    }

    public function testListByCityServiceShowsTestimonialsWithFallback(): void
    {
        $city = new City('Plovdiv');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Bath');
        $service->refreshSlugFrom($service->getName());

        $this->em->persist($city);
        $this->em->persist($service);

        $author = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');

        $withReviewUser = (new User())
            ->setEmail('with@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $withReview = new GroomerProfile($withReviewUser, $city, 'With Review', 'About');
        $withReview->refreshSlugFrom($withReview->getBusinessName());
        $withReview->addService($service);

        $withoutReviewUser = (new User())
            ->setEmail('without@example.com')
            ->setRoles([User::ROLE_GROOMER])
            ->setPassword('hash');
        $withoutReview = new GroomerProfile($withoutReviewUser, $city, 'Without Review', 'About');
        $withoutReview->refreshSlugFrom($withoutReview->getBusinessName());
        $withoutReview->addService($service);

        $this->em->persist($author);
        $this->em->persist($withReviewUser);
        $this->em->persist($withReview);
        $this->em->persist($withoutReviewUser);
        $this->em->persist($withoutReview);

        $review = new Review($withReview, $author, 5, 'Real review testimonial');
        $this->em->persist($review);

        $placeholder = (new Testimonial('Jane', 'Plovdiv', 'Placeholder testimonial'))
            ->markPlaceholder();
        $this->em->persist($placeholder);
        $this->em->flush();

        $this->client->request('GET', '/groomers/'.$city->getSlug().'/'.$service->getSlug());
        self::assertResponseIsSuccessful();
        $content = (string) $this->client->getResponse()->getContent();
        self::assertStringContainsString('Real review testimonial', $content);
        self::assertStringContainsString('Placeholder testimonial', $content);
        self::assertStringContainsString('This testimonial is a placeholder.', $content);
    }
}
