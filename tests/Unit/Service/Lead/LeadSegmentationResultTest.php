<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Service as GroomingService;
use App\Entity\User;
use App\Service\Lead\LeadSegmentationResult;
use PHPUnit\Framework\TestCase;

final class LeadSegmentationResultTest extends TestCase
{
    public function testAddAndSortRecipientsWithTieBreaker(): void
    {
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');

        $user = (new User())->setEmail('u@example.com')->setPassword('x')->setRoles([User::ROLE_GROOMER]);
        $withUser = new GroomerProfile($user, $city, 'Biz1', 'About');
        $withUser->addService($service);

        $noUser = new GroomerProfile(null, $city, 'Biz2', 'About');
        $noUser->addService($service);

        $res = new LeadSegmentationResult();
        $res->addRecipient($noUser, 'b@example.com', 1.0, ['dog']);
        $res->addRecipient($withUser, 'a@example.com', 1.0, ['dog']);

        // Equal scores, should prefer with linked user first after sorting
        $res->sortByScoreDesc();
        $recipients = $res->getRecipients();
        self::assertSame($withUser, $recipients[0]['profile']);
        self::assertSame('a@example.com', $recipients[0]['email']);
        self::assertSame(['dog'], $recipients[0]['matches']);
    }

    public function testAddExclusion(): void
    {
        $city = new City('X');
        $service = (new GroomingService())->setName('Groom');
        $p = new GroomerProfile(null, $city, 'Biz', 'About');
        $p->addService($service);

        $res = new LeadSegmentationResult();
        $res->addExclusion($p, 'no_email');
        $ex = $res->getExcluded();

        self::assertCount(1, $ex);
        self::assertSame($p, $ex[0]['profile']);
        self::assertSame('no_email', $ex[0]['reason']);
    }
}

