<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Sms;

use App\Service\Sms\NullSmsGateway;
use App\Service\Sms\SmsResult;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

final class NullSmsGatewayTest extends TestCase
{
    /** @var LoggerInterface&MockObject */
    private LoggerInterface $logger;

    protected function setUp(): void
    {
        $this->logger = $this->createMock(LoggerInterface::class);
    }

    public function testSendLogsAndReturnsOk(): void
    {
        $this->logger
            ->expects(self::once())
            ->method('info')
            ->with(
                self::stringContains('NullSmsGateway'),
                self::callback(function (array $context): bool {
                    return isset($context['to'], $context['length'])
                        && $context['to'] === '+15551234567'
                        && $context['length'] === 11;
                })
            );

        $gw = new NullSmsGateway($this->logger);
        $result = $gw->send('+15551234567', 'Hello World');

        self::assertInstanceOf(SmsResult::class, $result);
        self::assertTrue($result->isOk());
        self::assertSame('null', $result->getMessageId());
        self::assertNull($result->getError());
    }
}

