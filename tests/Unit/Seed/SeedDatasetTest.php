<?php

declare(strict_types=1);

namespace App\Tests\Unit\Seed;

use App\Entity\User;
use App\Seed\SeedDataset;
use PHPUnit\Framework\TestCase;

final class SeedDatasetTest extends TestCase
{
    public function testDefaultDatasetStructure(): void
    {
        $dataset = SeedDataset::default();

        self::assertNotEmpty($dataset->cities);
        self::assertSame('Sofia', $dataset->cities[0]['name']);
        self::assertGreaterThanOrEqual(5, count($dataset->cities));

        self::assertNotEmpty($dataset->services);
        self::assertSame('Mobile Dog Grooming', $dataset->services[0]['name']);

        self::assertGreaterThanOrEqual(6, count($dataset->users));
        self::assertSame('groomer1@example.com', $dataset->users[0]['email']);
        self::assertContains(User::ROLE_GROOMER, $dataset->users[0]['roles']);

        self::assertGreaterThanOrEqual(5, count($dataset->groomerProfiles));
        self::assertSame('Sofia Mobile Groomer', $dataset->groomerProfiles[0]['businessName']);
    }
}
