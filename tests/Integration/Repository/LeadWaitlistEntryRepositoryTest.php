<?php

declare(strict_types=1);

namespace App\Tests\Integration\Repository;

use App\Entity\City;
use App\Entity\Lead;
use App\Entity\LeadRecipient;
use App\Entity\LeadWaitlistEntry;
use App\Entity\Service;
use App\Repository\LeadWaitlistEntryRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

final class LeadWaitlistEntryRepositoryTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private LeadWaitlistEntryRepository $repo;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $tool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        /** @var LeadWaitlistEntryRepository $repo */
        $repo = $this->em->getRepository(LeadWaitlistEntry::class);
        $this->repo = $repo;
    }

    public function testFindByLeadReturnsWaitlistEntries(): void
    {
        $city = new City('Plovdiv');
        $service = (new Service())->setName('Mobile Dog Grooming');
        $lead = new Lead($city, $service, 'Owner', 'owner@example.com');
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->persist($lead);
        $this->em->flush();

        $recipient = new LeadRecipient($lead, 'g@example.com', 'hash', new \DateTimeImmutable('+1 day'));
        $this->em->persist($recipient);
        $this->em->flush();

        $w1 = new LeadWaitlistEntry($lead);
        $w2 = new LeadWaitlistEntry($lead, $recipient);
        $this->em->persist($w1);
        $this->em->persist($w2);
        $this->em->flush();
        $this->em->clear();

        $rows = $this->repo->findByLead($lead);
        self::assertCount(2, $rows);
        self::assertNull($rows[0]->getLeadRecipient());
        self::assertNotNull($rows[1]->getLeadRecipient());
    }
}

