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

        self::assertNotEmpty($dataset->services);
        self::assertSame('Mobile Dog Grooming', $dataset->services[0]['name']);

        self::assertCount(2, $dataset->users);
        self::assertSame('groomer@example.com', $dataset->users[0]['email']);
        self::assertContains(User::ROLE_GROOMER, $dataset->users[0]['roles']);

        self::assertCount(1, $dataset->groomerProfiles);
        self::assertSame('Sofia Mobile Groomer', $dataset->groomerProfiles[0]['businessName']);
    }
}
