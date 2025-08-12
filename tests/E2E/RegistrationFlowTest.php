<?php

declare(strict_types=1);

namespace App\Tests\E2E;

use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class RegistrationFlowTest extends WebTestCase
{
    public function testRegisterGroomer(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/register');
        self::assertResponseIsSuccessful();

        $email = uniqid('user_', true).'@example.com';
        $form = $crawler->selectButton('Register')->form([
            'registration_form[email]' => $email,
            'registration_form[plainPassword]' => 'secret',
            'registration_form[role]' => 'ROLE_GROOMER',
        ]);

        $client->submit($form);
        self::assertResponseRedirects('/');

        $userRepository = static::getContainer()->get(UserRepository::class);
        $user = $userRepository->findOneBy(['email' => $email]);
        self::assertNotNull($user);
        self::assertContains('ROLE_GROOMER', $user->getRoles());
    }
}
