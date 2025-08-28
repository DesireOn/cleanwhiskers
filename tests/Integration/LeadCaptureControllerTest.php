<?php

declare(strict_types=1);

namespace App\Tests\Integration;

use App\Entity\City;
use App\Entity\LeadCapture;
use App\Entity\Service;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LeadCaptureControllerTest extends WebTestCase
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

    public function testLeadCaptureCreatesEntity(): void
    {
        $city = new City('Varna');
        $city->refreshSlugFrom($city->getName());
        $service = (new Service())->setName('Clipping');
        $service->refreshSlugFrom($service->getName());
        $this->em->persist($city);
        $this->em->persist($service);
        $this->em->flush();

        $payload = [
            'name' => 'Jane',
            'email' => 'jane@example.com',
            'dogBreed' => 'Poodle',
            'city' => $city->getId(),
            'service' => $service->getId(),
            'website' => '',
        ];

        $this->client->request(
            'POST',
            '/lead-capture',
            [],
            [],
            ['CONTENT_TYPE' => 'application/json'],
            json_encode($payload),
        );

        self::assertResponseStatusCodeSame(201);
        $leads = $this->em->getRepository(LeadCapture::class)->findAll();
        self::assertCount(1, $leads);
        self::assertSame('jane@example.com', $leads[0]->getEmail());
    }
}
