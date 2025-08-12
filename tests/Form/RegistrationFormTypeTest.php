<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\RegistrationFormType;
use Symfony\Component\Form\Test\TypeTestCase;

class RegistrationFormTypeTest extends TypeTestCase
{
    public function testRoleChoices(): void
    {
        $form = $this->factory->create(RegistrationFormType::class);
        $choices = $form->get('role')->getConfig()->getOption('choices');

        self::assertSame([
            'Owner' => 'ROLE_OWNER',
            'Groomer' => 'ROLE_GROOMER',
        ], $choices);
    }

    public function testSubmitValidData(): void
    {
        $formData = [
            'email' => 'user@example.com',
            'plainPassword' => 'password',
            'role' => 'ROLE_OWNER',
        ];

        $form = $this->factory->create(RegistrationFormType::class);
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
        self::assertSame('ROLE_OWNER', $form->get('role')->getData());
    }
}
