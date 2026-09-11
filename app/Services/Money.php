<?php

namespace App\Services;

final class Money
{
    public static function cents(string $amount): int
    {
        if (! preg_match('/^([0-9]{1,14})(?:\.([0-9]{1,2}))?$/', $amount, $matches)) {
            throw new \InvalidArgumentException('Invalid monetary amount.');
        }

        return (int) $matches[1] * 100 + (int) str_pad($matches[2] ?? '', 2, '0');
    }

    public static function decimal(int $cents): string
    {
        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }
}
