<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Lead;

use App\Dto\Lead\LeadSubmissionDto;
use App\Repository\CityRepository;
use App\Repository\ServiceRepository;
use App\Service\Lead\LeadSubmissionValidator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class LeadSubmissionValidatorTest extends TestCase
{
    /** @var \Symfony\Component\Validator\Validator\ValidatorInterface&MockObject */
    private $validator;
    /** @var CityRepository&MockObject */
    private CityRepository $cities;
    /** @var ServiceRepository&MockObject */
    private ServiceRepository $services;

    protected function setUp(): void
    {
        $this->validator = $this->getMockBuilder(\Symfony\Component\Validator\Validator\ValidatorInterface::class)->getMock();
        $this->cities = $this->createMock(CityRepository::class);
        $this->services = $this->createMock(ServiceRepository::class);
    }

    public function testValidSubmissionReturnsNoErrors(): void
    {
        $dto = new LeadSubmissionDto();
        $dto->citySlug = 'sofia';
        $dto->serviceSlug = 'mobile-dog-grooming';
        $dto->fullName = 'John Doe';
        $dto->phone = '+1 555 000 1234';
        $dto->petType = 'Dog';
        $dto->email = 'owner@example.com';
        $dto->honeypot = null;
        $dto->captchaToken = 'ok';
        $dto->clientIp = '127.0.0.1';

        $this->validator->method('validate')->willReturn(new TestViolationList([]));
        $this->cities->method('findOneBySlug')->with('sofia')->willReturn(new \App\Entity\City('Sofia'));
        $this->services->method('findOneBySlug')->with('mobile-dog-grooming')->willReturn((new \App\Entity\Service())->setName('Mobile Dog Grooming'));
        $svc = new LeadSubmissionValidator($this->validator, $this->cities, $this->services);
        $errors = $svc->validate($dto);
        self::assertSame([], $errors);
    }

    public function testAggregatesErrorsFromAllSources(): void
    {
        $dto = new LeadSubmissionDto();
        $dto->citySlug = 'invalid-city';
        $dto->serviceSlug = 'invalid-service';
        $dto->fullName = '';
        $dto->phone = '123'; // invalid (too short)
        $dto->petType = '';
        $dto->honeypot = 'filled'; // bot
        $dto->captchaToken = 'bad';
        $dto->clientIp = '127.0.0.1';

        $violations = new TestViolationList([
            new TestViolation('Full name is required.'),
            new TestViolation('Please select your pet type.'),
        ]);
        $this->validator->method('validate')->willReturn($violations);
        $this->cities->method('findOneBySlug')->with('invalid-city')->willReturn(null);
        $this->services->method('findOneBySlug')->with('invalid-service')->willReturn(null);
        $svc = new LeadSubmissionValidator($this->validator, $this->cities, $this->services);
        $errors = $svc->validate($dto);

        self::assertContains('Full name is required.', $errors);
        self::assertContains('Please select your pet type.', $errors);
        self::assertContains('Invalid submission.', $errors);
        self::assertContains('Please select a valid city.', $errors);
        self::assertContains('Please select a valid service.', $errors);
        self::assertContains('Please provide a valid phone number.', $errors);
        // CAPTCHA is verified once at controller level now; validator no longer checks it.
    }
}

/**
 * Minimal implementations to support foreach iteration in tests.
 */
final class TestViolationList implements ConstraintViolationListInterface, \IteratorAggregate
{
    /** @var list<ConstraintViolationInterface> */
    private array $items;

    public function __construct(array $items)
    {
        $this->items = array_values($items);
    }

    public function add(ConstraintViolationInterface $violation): void { $this->items[] = $violation; }
    public function addAll(ConstraintViolationListInterface $otherList): void
    {
        foreach ($otherList as $v) { $this->items[] = $v; }
    }
    public function get(int $offset): ConstraintViolationInterface { return $this->items[$offset]; }
    public function has(int $offset): bool { return isset($this->items[$offset]); }
    public function set(int $offset, ConstraintViolationInterface $violation): void { $this->items[$offset] = $violation; }
    public function remove(int $offset): void { unset($this->items[$offset]); }
    public function __toString(): string { return 'violation-list'; }
    public function getIterator(): \Traversable { return new \ArrayIterator($this->items); }
    public function count(): int { return \count($this->items); }
    public function offsetExists($offset): bool { return isset($this->items[$offset]); }
    public function offsetGet($offset): mixed { return $this->items[$offset]; }
    public function offsetSet($offset, $value): void { $this->items[$offset] = $value; }
    public function offsetUnset($offset): void { unset($this->items[$offset]); }
}

final class TestViolation implements ConstraintViolationInterface
{
    public function __construct(private string $message) {}
    public function getMessage(): string|\Stringable { return $this->message; }
    public function getMessageTemplate(): string { return $this->message; }
    public function getParameters(): array { return []; }
    public function getPlural(): ?int { return null; }
    public function getRoot(): mixed { return null; }
    public function getPropertyPath(): string { return ''; }
    public function getInvalidValue(): mixed { return null; }
    public function getCode(): ?string { return null; }
    public function getConstraint(): ?\Symfony\Component\Validator\Constraint { return null; }
    public function getCause(): mixed { return null; }
    public function __toString(): string { return (string) $this->message; }
}
