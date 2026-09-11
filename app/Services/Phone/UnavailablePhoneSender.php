<?php

namespace App\Services\Phone;

class UnavailablePhoneSender implements PhoneSender
{
    public function available(): bool
    {
        return false;
    }

    public function send(string $phone, #[\SensitiveParameter] string $code): void
    {
        throw new \RuntimeException('Phone delivery is not configured.');
    }
}
