<?php

declare(strict_types=1);

namespace App\Tests\Functional\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\LeadRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LeadRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private LeadRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $tool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $this->repo = $this->em->getRepository(Lead::class);
    }

    private function makeCity(string $name = 'City'): City
    {
        $c = new City($name);
        $c->refreshSlugFrom($name);
        return $c;
    }

    private function makeService(string $name = 'Service'): Service
    {
        $s = (new Service())->setName($name);
        $s->refreshSlugFrom($name);
        return $s;
    }

    public function testFindPendingByCityAndService(): void
    {
        $city = $this->makeCity('Sofia');
        $service = $this->makeService('Bath');
        $otherCity = $this->makeCity('Varna');
        $otherService = $this->makeService('Clip');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($otherCity);
        $this->em->persist($otherService);
        $this->em->flush();

        $l1 = new Lead($city, $service, 'A', 'a@example.com');
        $l2 = new Lead($city, $service, 'B', 'b@example.com');
        $l3 = new Lead($city, $otherService, 'C', 'c@example.com'); // different service
        $l4 = new Lead($otherCity, $service, 'D', 'd@example.com'); // different city
        $l5 = new Lead($city, $service, 'E', 'e@example.com');
        $l5->setStatus(Lead::STATUS_CLAIMED); // not pending

        // Adjust createdAt to ensure ordering: l2 newest, then l1
        $now = new \DateTimeImmutable();
        $rp = new \ReflectionProperty(Lead::class, 'createdAt');
        $rp->setAccessible(true);
        $rp->setValue($l1, $now->modify('-2 minutes'));
        $rp->setValue($l2, $now->modify('-1 minutes'));

        $this->em->persist($l1);
        $this->em->persist($l2);
        $this->em->persist($l3);
        $this->em->persist($l4);
        $this->em->persist($l5);
        $this->em->flush();
        $this->em->clear();

        $result = $this->repo->findPendingByCityAndService($city, $service, 10);
        self::assertCount(2, $result);
        self::assertSame('B', $result[0]->getFullName());
        self::assertSame('A', $result[1]->getFullName());
    }

    public function testFindOneByOwnerTokenHash(): void
    {
        $city = $this->makeCity('Plovdiv');
        $service = $this->makeService('Clipping');
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->flush();

        $l = new Lead($city, $service, 'Owner', 'o@example.com');
        $l->setOwnerTokenHash('hash-123');
        $this->em->persist($l);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repo->findOneByOwnerTokenHash('hash-123');
        self::assertInstanceOf(Lead::class, $found);
        self::assertSame('Owner', $found->getFullName());
    }

    public function testFindLastClaimedByGroomer(): void
    {
        $city = $this->makeCity('Burgas');
        $service = $this->makeService('Trim');
        $user = (new User())->setEmail('g@example.com')->setRoles([User::ROLE_GROOMER])->setPassword('hash');
        $groomer = new GroomerProfile($user, $city, 'Pro', 'About');
        $groomer->refreshSlugFrom('Pro');

        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($user);
        $this->em->persist($groomer);
        $this->em->flush();

        $older = new Lead($city, $service, 'Old', 'old@example.com');
        $older->setStatus(Lead::STATUS_CLAIMED);
        $older->setClaimedBy($groomer);
        $older->setClaimedAt(new \DateTimeImmutable('-2 hours'));

        $newer = new Lead($city, $service, 'New', 'new@example.com');
        $newer->setStatus(Lead::STATUS_CLAIMED);
        $newer->setClaimedBy($groomer);
        $newer->setClaimedAt(new \DateTimeImmutable('-1 hours'));

        $this->em->persist($older);
        $this->em->persist($newer);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repo->findLastClaimedByGroomer($groomer);
        self::assertInstanceOf(Lead::class, $found);
        self::assertSame('New', $found->getFullName());
    }

    public function testFindRecentByFingerprint(): void
    {
        $city = $this->makeCity('Varna');
        $service = $this->makeService('Wash');
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->flush();

        $lead = new Lead($city, $service, 'Owner', 'o@example.com');
        $lead->setSubmissionFingerprint('fp-abc');
        $this->em->persist($lead);
        $this->em->flush();
        $this->em->clear();

        $threshold = new \DateTimeImmutable('-10 minutes');
        $found = $this->repo->findRecentByFingerprint('fp-abc', $threshold);
        self::assertInstanceOf(Lead::class, $found);
        self::assertSame('Owner', $found->getFullName());
    }
}

