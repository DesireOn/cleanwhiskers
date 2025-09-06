<?php

namespace App\Service\Sms;

final class SmsResult
{
    private bool $ok;
    private ?string $messageId;
    private ?string $error;

    private function __construct(bool $ok, ?string $messageId = null, ?string $error = null)
    {
        $this->ok = $ok;
        $this->messageId = $messageId;
        $this->error = $error;
    }

    public static function ok(?string $messageId = null): self
    {
        return new self(true, $messageId, null);
    }

    public static function error(string $error): self
    {
        return new self(false, null, $error);
    }

    public function isOk(): bool
    {
        return $this->ok;
    }

    public function getMessageId(): ?string
    {
        return $this->messageId;
    }

    public function getError(): ?string
    {
        return $this->error;
    }
}

