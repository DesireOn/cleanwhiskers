<?php

declare(strict_types=1);

namespace App\Tests\Unit\Service\Sms;

use App\Service\Sms\SmsResult;
use PHPUnit\Framework\TestCase;

final class SmsResultTest extends TestCase
{
    public function testOkFactorySetsOkAndMessageId(): void
    {
        $res = SmsResult::ok('abc123');

        self::assertTrue($res->isOk());
        self::assertSame('abc123', $res->getMessageId());
        self::assertNull($res->getError());
    }

    public function testErrorFactorySetsError(): void
    {
        $res = SmsResult::error('network-failure');

        self::assertFalse($res->isOk());
        self::assertNull($res->getMessageId());
        self::assertSame('network-failure', $res->getError());
    }
}

