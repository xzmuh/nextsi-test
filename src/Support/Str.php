<?php

declare(strict_types=1);

namespace App\Support;

final class Str
{
    public static function digits(string $value): string
    {
        return preg_replace('/\D+/', '', $value) ?? '';
    }
}