<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\Service;
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
        $container = self::getContainer();
        $this->em = $container->get('doctrine')->getManager();
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $tool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        /** @var LeadRepository $repo */
        $repo = $this->em->getRepository(Lead::class);
        $this->repo = $repo;
    }

    private function seedCityService(): array
    {
        $city = new City('Sofia');
        $city->refreshSlugFrom('Sofia');
        $service = (new Service())->setName('Mobile Dog Grooming');
        $service->refreshSlugFrom('Mobile Dog Grooming');
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->flush();
        return [$city, $service];
    }

    public function testFindPendingByCityAndServiceReturnsOnlyPendingSorted(): void
    {
        [$city, $service] = $this->seedCityService();

        $lead1 = new Lead($city, $service, 'A', 'a@example.com');
        $lead2 = new Lead($city, $service, 'B', 'b@example.com');
        $lead3 = new Lead($city, $service, 'C', 'c@example.com');
        $lead2->setStatus(Lead::STATUS_CLAIMED);

        $this->em->persist($lead1);
        $this->em->persist($lead2);
        $this->em->persist($lead3);
        $this->em->flush();

        $rows = $this->repo->findPendingByCityAndService($city, $service, 10);

        self::assertCount(2, $rows);
        self::assertSame(Lead::STATUS_PENDING, $rows[0]->getStatus());
        self::assertSame(Lead::STATUS_PENDING, $rows[1]->getStatus());
        // Sorted by createdAt DESC, so last persisted (lead3) should come first
        self::assertSame('C', $rows[0]->getFullName());
    }

    public function testFindOneByOwnerTokenHash(): void
    {
        [$city, $service] = $this->seedCityService();
        $lead = new Lead($city, $service, 'Owner', 'owner@example.com');
        $lead->setOwnerTokenHash('hash123');
        $this->em->persist($lead);
        $this->em->flush();
        $this->em->clear();

        $found = $this->repo->findOneByOwnerTokenHash('hash123');
        self::assertNotNull($found);
        self::assertSame('Owner', $found->getFullName());

        self::assertNull($this->repo->findOneByOwnerTokenHash('missing'));
    }

    public function testFindLastClaimedByGroomer(): void
    {
        [$city, $service] = $this->seedCityService();
        $g = new GroomerProfile(null, $city, 'Biz', 'About');
        $this->em->persist($g);

        $l1 = new Lead($city, $service, 'A', 'a@example.com');
        $l1->setStatus(Lead::STATUS_CLAIMED);
        $l1->setClaimedBy($g);
        $l1->setClaimedAt(new \DateTimeImmutable('-2 hours'));

        $l2 = new Lead($city, $service, 'B', 'b@example.com');
        $l2->setStatus(Lead::STATUS_CLAIMED);
        $l2->setClaimedBy($g);
        $l2->setClaimedAt(new \DateTimeImmutable('-1 hour'));

        $this->em->persist($l1);
        $this->em->persist($l2);
        $this->em->flush();

        $found = $this->repo->findLastClaimedByGroomer($g);
        self::assertNotNull($found);
        self::assertSame('B', $found->getFullName());
    }

    public function testFindRecentByFingerprintHonorsThreshold(): void
    {
        [$city, $service] = $this->seedCityService();
        $lead = new Lead($city, $service, 'A', 'a@example.com');
        $lead->setSubmissionFingerprint('fp');
        $this->em->persist($lead);
        $this->em->flush();

        // threshold in the past -> should find
        $past = new \DateTimeImmutable('-10 minutes');
        $found = $this->repo->findRecentByFingerprint('fp', $past);
        self::assertNotNull($found);

        // threshold in the future -> should not find
        $future = new \DateTimeImmutable('+10 minutes');
        $notFound = $this->repo->findRecentByFingerprint('fp', $future);
        self::assertNull($notFound);
    }
}

