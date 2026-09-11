<?php

namespace App\Services\Phone;

interface PhoneSender
{
    public function available(): bool;

    /** Return only when the provider accepts delivery; never log the code. */
    public function send(string $phone, #[\SensitiveParameter] string $code): void;
}
