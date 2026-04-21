<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

use App\Utils\Str;

final class DocumentValidator
{
    public static function normalize(string $value): string
    {
        return Str::digits($value);
    }

    public static function isValid(string $value): bool
    {
        $digits = self::normalize($value);
        return self::isValidCpf($digits) || self::isValidCnpj($digits);
    }

    private static function isValidCpf(string $cpf): bool
    {
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            return false;
        }

        for ($position = 9; $position < 11; $position++) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * (($position + 1) - $index);
            }

            $digit = ((10 * $sum) % 11) % 10;

            if ($digit !== (int) $cpf[$position]) {
                return false;
            }
        }

        return true;
    }

    private static function isValidCnpj(string $cnpj): bool
    {
        if (strlen($cnpj) !== 14 || preg_match('/^(\d)\1{13}$/', $cnpj) === 1) {
            return false;
        }

        $weights = [
            [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
            [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        ];

        foreach ($weights as $checkIndex => $weightSet) {
            $sum = 0;

            foreach ($weightSet as $index => $weight) {
                $sum += (int) $cnpj[$index] * $weight;
            }

            $remainder = $sum % 11;
            $digit = $remainder < 2 ? 0 : 11 - $remainder;

            if ($digit !== (int) $cnpj[12 + $checkIndex]) {
                return false;
            }
        }

        return true;
    }
}