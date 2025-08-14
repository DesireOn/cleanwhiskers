<?php

declare(strict_types=1);

namespace App\Tests\Security;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Review;
use App\Entity\User;
use App\Repository\ReviewRepository;
use App\Security\ReviewIntegrityVoter;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Authorization\Voter\VoterInterface;

final class ReviewIntegrityTest extends KernelTestCase
{
    private EntityManagerInterface $em;
    private ReviewIntegrityVoter $voter;

    protected function setUp(): void
    {
        self::bootKernel();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $tool = new SchemaTool($this->em);
        $tool->dropSchema($this->em->getMetadataFactory()->getAllMetadata());
        $tool->createSchema($this->em->getMetadataFactory()->getAllMetadata());
        $repository = $this->em->getRepository(Review::class);
        \assert($repository instanceof ReviewRepository);
        $this->voter = new ReviewIntegrityVoter($repository);
    }

    public function testGroomerCannotReviewOwnProfile(): void
    {
        $city = new City('Sofia');
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $profile = new GroomerProfile($groomerUser, $city, 'Best Groomers', 'About');

        $this->em->persist($city);
        $this->em->persist($groomerUser);
        $this->em->persist($profile);
        $this->em->flush();

        $token = new UsernamePasswordToken($groomerUser, 'main', $groomerUser->getRoles());
        $result = $this->voter->vote($token, $profile, [ReviewIntegrityVoter::CREATE]);

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testUserCannotReviewSameGroomerTwice(): void
    {
        $city = new City('Sofia');
        $author = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $profile = new GroomerProfile($groomerUser, $city, 'Best Groomers', 'About');

        $this->em->persist($city);
        $this->em->persist($author);
        $this->em->persist($groomerUser);
        $this->em->persist($profile);
        $this->em->flush();

        $existing = new Review($profile, $author, 5, 'Great');
        $this->em->persist($existing);
        $this->em->flush();

        $token = new UsernamePasswordToken($author, 'main', $author->getRoles());
        $result = $this->voter->vote($token, $profile, [ReviewIntegrityVoter::CREATE]);

        self::assertSame(VoterInterface::ACCESS_DENIED, $result);
    }

    public function testUserCanReviewGroomerOnce(): void
    {
        $city = new City('Sofia');
        $author = (new User())
            ->setEmail('owner@example.com')
            ->setPassword('hash');
        $groomerUser = (new User())
            ->setEmail('groomer@example.com')
            ->setPassword('hash')
            ->setRoles([User::ROLE_GROOMER]);
        $profile = new GroomerProfile($groomerUser, $city, 'Best Groomers', 'About');

        $this->em->persist($city);
        $this->em->persist($author);
        $this->em->persist($groomerUser);
        $this->em->persist($profile);
        $this->em->flush();

        $token = new UsernamePasswordToken($author, 'main', $author->getRoles());
        $result = $this->voter->vote($token, $profile, [ReviewIntegrityVoter::CREATE]);

        self::assertSame(VoterInterface::ACCESS_GRANTED, $result);
    }
}
