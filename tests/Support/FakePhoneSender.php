<?php

namespace Tests\Support;

use App\Services\Phone\PhoneSender;

class FakePhoneSender implements PhoneSender
{
    public array $messages = [];

    public bool $fail = false;

    public function available(): bool
    {
        return true;
    }

    public function send(string $phone, #[\SensitiveParameter] string $code): void
    {
        if ($this->fail) {
            throw new \RuntimeException('Provider failed');
        }
        $this->messages[] = ['phone' => $phone, 'code' => $code];
    }
}
