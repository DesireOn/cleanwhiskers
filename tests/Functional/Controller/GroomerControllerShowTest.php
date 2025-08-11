<?php

declare(strict_types=1);

namespace App\Tests\Functional\Controller;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\AbstractBrowser;

final class GroomerControllerShowTest extends WebTestCase
{
    private AbstractBrowser $client;
    private EntityManagerInterface $em;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = $this->client->getContainer()->get('doctrine')->getManager();
        $schemaTool = new SchemaTool($this->em);
        $metadata = $this->em->getMetadataFactory()->getAllMetadata();
        $schemaTool->dropSchema($metadata);
        $schemaTool->createSchema($metadata);
    }

    public function testShowReturns200WithBusinessName(): void
    {
        $user = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('password')
            ->setRoles([User::ROLE_GROOMER]);
        $city = new City('Sofia');
        $groomer = new GroomerProfile($user, $city, 'Groomer Co', 'We groom pets', 'groomer-co');
        $this->em->persist($user);
        $this->em->persist($city);
        $this->em->persist($groomer);
        $this->em->flush();

        $this->client->request('GET', '/groomers/groomer-co');
        $this->assertResponseIsSuccessful();
        $content = $this->client->getResponse()->getContent();
        \assert(is_string($content));
        $this->assertStringContainsString('Groomer Co', $content);
        $this->assertStringContainsString('Sofia', $content);
    }

    public function testUnknownSlugReturns404(): void
    {
        $this->client->request('GET', '/groomers/unknown');
        $this->assertResponseStatusCodeSame(404);
    }
}
