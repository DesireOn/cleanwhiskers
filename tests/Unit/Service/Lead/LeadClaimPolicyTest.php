<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\Service as GroomingService;
use App\Entity\User;
use App\Repository\LeadRepository;
use App\Service\Lead\LeadClaimPolicy;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LeadClaimPolicyTest extends TestCase
{
    /** @var LeadRepository&MockObject */
    private LeadRepository $leads;

    private GroomerProfile $groomer;

    protected function setUp(): void
    {
        $this->leads = $this->createMock(LeadRepository::class);

        $city = new City('Sofia');
        $user = (new User())->setEmail('g@example.com')->setPassword('x')->setRoles([User::ROLE_GROOMER]);
        $this->groomer = new GroomerProfile($user, $city, 'Biz', 'About');
    }

    public function testAllowsWhenNoPreviousClaim(): void
    {
        $this->leads->method('findLastClaimedByGroomer')->with($this->groomer)->willReturn(null);
        $policy = new LeadClaimPolicy($this->leads);

        $allowance = $policy->canClaim($this->groomer);
        self::assertTrue($allowance->isAllowed());
    }

    public function testAllowsWhenPreviousHasNoClaimedAt(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_CLAIMED);
        // claimedAt intentionally left null
        $this->leads->method('findLastClaimedByGroomer')->willReturn($lead);

        $policy = new LeadClaimPolicy($this->leads);
        $allowance = $policy->canClaim($this->groomer);
        self::assertTrue($allowance->isAllowed());
    }

    public function testDeniesWithinDefaultCooldownAndReturnsRemaining(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_CLAIMED);
        $lead->setClaimedAt(new \DateTimeImmutable('-30 minutes'));
        $this->leads->method('findLastClaimedByGroomer')->willReturn($lead);

        $policy = new LeadClaimPolicy($this->leads); // default 2 hours
        $allowance = $policy->canClaim($this->groomer);

        self::assertFalse($allowance->isAllowed());
        self::assertSame('cooldown', $allowance->getReason());
        self::assertNotNull($allowance->getNextAllowedAt());
        self::assertNotNull($allowance->getRemainingMinutes());
        self::assertGreaterThan(0, $allowance->getRemainingMinutes());
    }

    public function testRespectsCustomCooldownHours(): void
    {
        $lead = $this->makeLead();
        $lead->setStatus(Lead::STATUS_CLAIMED);
        $lead->setClaimedAt(new \DateTimeImmutable('-80 minutes'));
        $this->leads->method('findLastClaimedByGroomer')->willReturn($lead);

        $policy = new LeadClaimPolicy($this->leads, cooldownHours: 1); // 1-hour cooldown
        $allowance = $policy->canClaim($this->groomer);

        self::assertTrue($allowance->isAllowed());
    }

    private function makeLead(): Lead
    {
        $city = new City('Sofia');
        $service = (new GroomingService())->setName('Mobile Dog Grooming');
        return new Lead($city, $service, 'Owner', 'o@example.com');
    }
}

