<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Entity\City;
use App\Entity\GroomerProfile;
use App\Entity\Lead;
use App\Entity\Service;
use App\Entity\User;
use App\Repository\EmailSuppressionRepository;
use App\Repository\GroomerProfileRepository;
use App\Service\Lead\LeadSegmentationService;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

final class LeadSegmentationServiceTest extends TestCase
{
    private City $city;
    private Service $service;

    /** @var GroomerProfileRepository&MockObject */
    private GroomerProfileRepository $profiles;

    /** @var EmailSuppressionRepository&MockObject */
    private EmailSuppressionRepository $suppressions;

    protected function setUp(): void
    {
        $this->city = new City('Test City');
        $this->service = (new Service())->setName('Mobile Dog Grooming');
        $this->service->refreshSlugFrom(Service::MOBILE_DOG_GROOMING);

        $this->profiles = $this->createMock(GroomerProfileRepository::class);
        $this->suppressions = $this->createMock(EmailSuppressionRepository::class);
    }

    public function testDogSmallMatchesWithVerifiedBonus(): void
    {
        $lead = new Lead($this->city, $this->service, 'John Doe', 'john@example.com');
        $lead->setPetType('Dog');
        $lead->setBreedSize('Small');

        $p = $this->profile(
            outreachEmail: 'a@example.com',
            userEmail: null,
            servicesOffered: 'Mobile dog grooming for small breeds',
            specialties: ['poodle'],
            badges: ['Verified']
        );

        $this->profiles
            ->method('findByCityAndService')
            ->with($this->city, $this->service, $this->anything(), $this->anything())
            ->willReturn([$p]);

        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $result = $svc->findMatchingRecipients($lead);

        $recipients = $result->getRecipients();
        self::assertCount(1, $recipients);
        self::assertSame('a@example.com', $recipients[0]['email']);
        // Expected score: 1.0 (pet) + 0.7 (size) + 0.1 (verified)
        self::assertGreaterThanOrEqual(1.8 - 0.001, $recipients[0]['score']);
        self::assertContains('dog', $recipients[0]['matches']);
        self::assertTrue(in_array('small', $recipients[0]['matches'], true) || $this->hasAnyPrefix($recipients[0]['matches'], 'small'));
    }

    public function testCatNoSizeStillMatches(): void
    {
        $lead = new Lead($this->city, $this->service, 'Jane Doe', 'jane@example.com');
        $lead->setPetType('Cat');
        $lead->setBreedSize(null);

        $p = $this->profile(
            outreachEmail: null,
            userEmail: 'b@example.com',
            servicesOffered: 'Cat grooming and feline care',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        $recipients = $res->getRecipients();
        self::assertCount(1, $recipients);
        self::assertSame('b@example.com', $recipients[0]['email']);
        self::assertGreaterThanOrEqual(1.0 - 0.001, $recipients[0]['score']);
        self::assertContains('cat', $recipients[0]['matches']);
    }

    public function testNoFuzzyMatchWhenSpecificityProvided(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        $lead->setPetType('Dog');
        $lead->setBreedSize('Large');

        $p = $this->profile(
            outreachEmail: 'c@example.com',
            userEmail: null,
            servicesOffered: 'Mobile grooming for cats only',
            specialties: ['feline care'],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        self::assertCount(0, $res->getRecipients());
        $excluded = $res->getExcluded();
        self::assertCount(1, $excluded);
        self::assertSame('no_fuzzy_match', $excluded[0]['reason']);
    }

    public function testIncludeWithoutSpecificityGetsBaselineScore(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        // no petType and breedSize

        $p = $this->profile(
            outreachEmail: 'd@example.com',
            userEmail: null,
            servicesOffered: 'Full-service mobile grooming',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        $recipients = $res->getRecipients();
        self::assertCount(1, $recipients);
        self::assertSame('d@example.com', $recipients[0]['email']);
        self::assertSame(0.1, $recipients[0]['score']);
    }

    public function testSuppressedEmailExcluded(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        $lead->setPetType('Dog');

        $p = $this->profile(
            outreachEmail: 'e@example.com',
            userEmail: null,
            servicesOffered: 'Dog grooming',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        $this->suppressions->method('isSuppressed')->willReturnCallback(fn(string $email) => $email === 'e@example.com');

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        self::assertCount(0, $res->getRecipients());
        $excluded = $res->getExcluded();
        self::assertCount(1, $excluded);
        self::assertSame('email_suppressed', $excluded[0]['reason']);
    }

    public function testSuppressedEmailCheckIsCaseInsensitive(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        $lead->setPetType('Dog');

        $p = $this->profile(
            outreachEmail: 'Upper@Example.com',
            userEmail: null,
            servicesOffered: 'Dog grooming',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        // Return true only if lowercased is checked
        $this->suppressions->method('isSuppressed')->willReturnCallback(function (string $email): bool {
            return $email === 'upper@example.com';
        });

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        self::assertCount(0, $res->getRecipients());
        $excluded = $res->getExcluded();
        self::assertSame('email_suppressed', $excluded[0]['reason']);
    }

    public function testPetTypeSynonymCanineMatchesDog(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        $lead->setPetType('Dog');

        $p = $this->profile(
            outreachEmail: 'syn@example.com',
            userEmail: null,
            servicesOffered: 'Expert CANINE styling and spa',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        $recipients = $res->getRecipients();
        self::assertCount(1, $recipients);
        self::assertGreaterThanOrEqual(1.0 - 0.001, $recipients[0]['score']);
        self::assertContains('canine', $recipients[0]['matches']);
    }

    public function testBreedSizeSynonymsMatchXLToLarge(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        $lead->setBreedSize('Large');

        $p = $this->profile(
            outreachEmail: 'xl@example.com',
            userEmail: null,
            servicesOffered: 'XL dog grooming',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        $recipients = $res->getRecipients();
        self::assertCount(1, $recipients);
        // Only size matched, so ~0.7
        self::assertGreaterThanOrEqual(0.7 - 0.001, $recipients[0]['score']);
        self::assertTrue(in_array('xl', $recipients[0]['matches'], true) || $this->hasAnyPrefix($recipients[0]['matches'], 'xl'));
    }

    public function testSubstringFuzzyMiniatureMatchesMini(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        $lead->setBreedSize('mini');

        $p = $this->profile(
            outreachEmail: 'mini@example.com',
            userEmail: null,
            servicesOffered: 'Grooming for miniature breeds',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        $recipients = $res->getRecipients();
        self::assertCount(1, $recipients);
        self::assertGreaterThanOrEqual(0.7 - 0.001, $recipients[0]['score']);
    }

    public function testSpecialtiesArrayContributesToPetTypeMatch(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        $lead->setPetType('Cat');

        $p = $this->profile(
            outreachEmail: 'spec@example.com',
            userEmail: null,
            servicesOffered: '',
            specialties: ['Feline care', 'Gentle handling'],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$p]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        $recipients = $res->getRecipients();
        self::assertCount(1, $recipients);
        self::assertContains('feline', $recipients[0]['matches']);
        self::assertGreaterThanOrEqual(1.0 - 0.001, $recipients[0]['score']);
    }

    public function testSortingTiePrefersProfilesWithLinkedUser(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');
        $lead->setPetType('Dog');

        $withUser = $this->profile(
            outreachEmail: null,
            userEmail: 'u@example.com',
            servicesOffered: 'Dog grooming',
            specialties: [],
            badges: []
        );
        $withoutUser = $this->profile(
            outreachEmail: 'z@example.com',
            userEmail: null,
            servicesOffered: 'Dog grooming',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$withoutUser, $withUser]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        $recipients = $res->getRecipients();
        self::assertCount(2, $recipients);
        // Same scores; profile with linked user should come first
        self::assertSame($withUser, $recipients[0]['profile']);
        self::assertSame('u@example.com', $recipients[0]['email']);
    }

    public function testEmailResolutionPrefersOutreachThenUserAndHandlesMissing(): void
    {
        $lead = new Lead($this->city, $this->service, 'Owner', 'o@example.com');

        $withOutreachAndUser = $this->profile(
            outreachEmail: 'outreach@example.com',
            userEmail: 'user1@example.com',
            servicesOffered: 'Dog grooming',
            specialties: [],
            badges: []
        );
        $withUserOnly = $this->profile(
            outreachEmail: null,
            userEmail: 'user2@example.com',
            servicesOffered: 'Dog grooming',
            specialties: [],
            badges: []
        );
        $withNone = $this->profile(
            outreachEmail: null,
            userEmail: null,
            servicesOffered: 'Dog grooming',
            specialties: [],
            badges: []
        );

        $this->profiles->method('findByCityAndService')->willReturn([$withOutreachAndUser, $withUserOnly, $withNone]);
        $this->suppressions->method('isSuppressed')->willReturn(false);

        $svc = new LeadSegmentationService($this->profiles, $this->suppressions);
        $res = $svc->findMatchingRecipients($lead);

        $recipients = $res->getRecipients();
        $excluded = $res->getExcluded();

        $emails = array_map(static fn($r) => $r['email'], $recipients);
        self::assertContains('outreach@example.com', $emails, 'Uses outreach email when available');
        self::assertContains('user2@example.com', $emails, 'Falls back to user email when outreach missing');
        self::assertTrue(array_reduce($excluded, fn($carry, $e) => $carry || $e['profile'] === $withNone && $e['reason'] === 'no_email_available', false));
    }

    private function profile(
        ?string $outreachEmail,
        ?string $userEmail,
        string $servicesOffered,
        array $specialties,
        array $badges
    ): GroomerProfile {
        $user = null;
        if (null !== $userEmail) {
            $user = (new User())
                ->setEmail($userEmail)
                ->setRoles([User::ROLE_GROOMER])
                ->setPassword('hash');
        }

        $p = new GroomerProfile($user, $this->city, 'Biz', 'About biz');
        $p->refreshSlugFrom('biz');
        $p->addService($this->service);
        $p->setOutreachEmail($outreachEmail);
        $p->setServicesOffered($servicesOffered);
        $p->setSpecialties($specialties);
        $p->setBadges($badges);

        return $p;
    }

    private function hasAnyPrefix(array $values, string $prefix): bool
    {
        foreach ($values as $v) {
            if (str_starts_with($v, $prefix)) {
                return true;
            }
        }
        return false;
    }
}
