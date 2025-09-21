<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\Service;
use App\Repository\LeadRecipientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LeadRecipientRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private LeadRecipientRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $tool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        /** @var LeadRecipientRepository $repo */
        $repo = $this->em->getRepository(LeadRecipient::class);
        $this->repo = $repo;
    }

    public function testFindByLeadReturnsRecipientsForLeadOrderedById(): void
    {
        $city = new City('Sofia');
        $service = (new Service())->setName('Mobile Dog Grooming');
        $lead1 = new Lead($city, $service, 'Owner', 'owner@example.com');
        $lead2 = new Lead($city, $service, 'Owner2', 'owner2@example.com');
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($lead1);
        $this->em->persist($lead2);
        $this->em->flush();

        $r1 = new LeadRecipient($lead1, 'a@example.com', 'h1', new \DateTimeImmutable('+1 day'));
        $r2 = new LeadRecipient($lead1, 'b@example.com', 'h2', new \DateTimeImmutable('+1 day'));
        $r3 = new LeadRecipient($lead2, 'c@example.com', 'h3', new \DateTimeImmutable('+1 day'));
        $this->em->persist($r1);
        $this->em->persist($r2);
        $this->em->persist($r3);
        $this->em->flush();
        $this->em->clear();

        $rows = $this->repo->findByLead($lead1);
        self::assertCount(2, $rows);
        self::assertSame('a@example.com', $rows[0]->getEmail());
        self::assertSame('b@example.com', $rows[1]->getEmail());
    }
}

