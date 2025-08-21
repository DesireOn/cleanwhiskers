<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use PHPUnit\Framework\TestCase;

if (!class_exists(PantherTestCase::class)) {
    class FeaturedGroomersCarouselTest extends TestCase
    {
        public function testPantherMissing(): void
        {
            $this->markTestSkipped('Panther not installed');
        }
    }

    return;
}

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\Service;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Component\Panther\PantherTestCase;

final class FeaturedGroomersCarouselTest extends PantherTestCase
{
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $schemaTool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $schemaTool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
    }

    public function testCarouselButtonsAndBookNow(): void
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Grooming');
        $service->refreshSlugFrom($service->getName());
        $this->em->persist($city);
        $this->em->persist($service);

        for ($i = 0; $i < 5; ++$i) {
            $gUser = (new User())
                ->setEmail(sprintf('g%d@example.com', $i))
                ->setPassword('hash')
                ->setRoles([User::ROLE_GROOMER]);
            $author = (new User())
                ->setEmail(sprintf('a%d@example.com', $i))
                ->setPassword('hash');
            $profile = new GroomerProfile($gUser, $city, 'Groomer '.$i, 'About');
            $profile->addService($service);
            $profile->setPriceRange('$10');
            $this->em->persist($gUser);
            $this->em->persist($author);
            $this->em->persist($profile);
            $this->em->persist(new Review($profile, $author, 5, 'Great'));
        }
        $this->em->flush();

        $client = self::createPantherClient();
        $client->request('GET', '/');

        $arrowsVisible = $client->executeScript(<<<'JS'
const prev = document.querySelector('[data-carousel-prev]');
const next = document.querySelector('[data-carousel-next]');
function isVisible(btn) {
    const rect = btn.getBoundingClientRect();
    const el = document.elementFromPoint(rect.left + rect.width / 2, rect.top + rect.height / 2);
    return el === btn;
}
return [isVisible(prev), isVisible(next)];
JS);

        self::assertSame([true, true], $arrowsVisible);

        $initial = $client->executeScript('return document.querySelector(".carousel__track").scrollLeft;');
        $client->executeScript('document.querySelector("[data-carousel-next]").click();');
        $afterNext = $client->executeScript('return document.querySelector(".carousel__track").scrollLeft;');
        self::assertGreaterThan($initial, $afterNext);

        $client->executeScript('document.querySelector("[data-carousel-prev]").click();');
        $afterPrev = $client->executeScript('return document.querySelector(".carousel__track").scrollLeft;');
        self::assertEquals($initial, $afterPrev);

        $client->waitForReload(function () use ($client) {
            $client->executeScript('document.querySelector(".card-groomer__book").click();');
        });

        $path = parse_url($client->getCurrentURL(), PHP_URL_PATH);
        self::assertStringStartsWith('/groomers/', (string) $path);
    }
}
