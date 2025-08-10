<?php

declare(strict_types=1);

namespace App\Tests\Unit\Entity;

use App\Entity\Review;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class ReviewValidationTest extends KernelTestCase
{
    public function testRatingOutOfRangeFailsValidation(): void
    {
        self::bootKernel();
        $validator = self::getContainer()->get(ValidatorInterface::class);
        $review = new Review();
        $review->setRating(6);
        $violations = $validator->validate($review);
        self::assertGreaterThan(0, $violations->count());
    }
}
