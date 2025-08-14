<?php

declare(strict_types=1);

namespace App\Tests\Form;

use App\Form\ReviewFormType;
use Symfony\Component\Form\Extension\Validator\ValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\Range;
use Symfony\Component\Validator\Validation;

final class ReviewFormTypeTest extends TypeTestCase
{
    protected function getExtensions(): array
    {
        return [
            new ValidatorExtension(Validation::createValidator()),
        ];
    }

    public function testConstraints(): void
    {
        $form = $this->factory->create(ReviewFormType::class);

        $ratingConstraints = $form->get('rating')->getConfig()->getOption('constraints');
        $range = null;
        foreach ($ratingConstraints as $constraint) {
            if ($constraint instanceof Range) {
                $range = $constraint;
                break;
            }
        }
        self::assertNotNull($range);
        self::assertSame(1, $range->min);
        self::assertSame(5, $range->max);

        $commentConstraints = $form->get('comment')->getConfig()->getOption('constraints');
        $length = null;
        foreach ($commentConstraints as $constraint) {
            if ($constraint instanceof Length) {
                $length = $constraint;
                break;
            }
        }
        self::assertNotNull($length);
        self::assertSame(1000, $length->max);
    }

    public function testSubmitValidData(): void
    {
        $form = $this->factory->create(ReviewFormType::class);
        $formData = ['rating' => 5, 'comment' => 'Great!'];
        $form->submit($formData);

        self::assertTrue($form->isSynchronized());
        self::assertTrue($form->isValid());
    }

    public function testSubmitInvalidData(): void
    {
        $form = $this->factory->create(ReviewFormType::class);
        $formData = ['rating' => 6, 'comment' => str_repeat('a', 1001)];
        $form->submit($formData);

        self::assertFalse($form->isValid());
        self::assertSame(6, $form->get('rating')->getData());
        self::assertSame(str_repeat('a', 1001), $form->get('comment')->getData());
    }
}
