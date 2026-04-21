<?php

declare(strict_types=1);

namespace App\Infrastructure\Validation;

use App\Domain\User\Role;
use App\Exceptions\HttpException;
use App\Support\Str;

final class UserInputValidator
{
    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    public static function validateLogin(array $input): array
    {
        $email = strtolower(trim((string) ($input['email'] ?? '')));
        $password = (string) ($input['password'] ?? '');

        if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw HttpException::validation('A valid email is required');
        }

        if ($password === '') {
            throw HttpException::validation('Password is required');
        }

        return [
            'email' => $email,
            'password' => $password,
        ];
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    public static function validateCreate(array $input): array
    {
        $data = self::normalize($input);

        foreach (['name', 'email', 'password', 'phone', 'document', 'role'] as $field) {
            if ($data[$field] === '') {
                throw HttpException::validation(sprintf('%s is required', $field));
            }
        }

        self::assertNormalized($data, true);

        return $data;
    }

    /**
     * @param array<string, mixed> $input
     * @param array<string, mixed> $current
     * @return array<string, string>
     */
    public static function validateUpdate(array $input, array $current): array
    {
        $data = [
            'name' => (string) ($current['name'] ?? ''),
            'email' => (string) ($current['email'] ?? ''),
            'phone' => (string) ($current['phone'] ?? ''),
            'document' => (string) ($current['document'] ?? ''),
            'role' => (string) ($current['role'] ?? Role::USER),
        ];

        if (array_key_exists('name', $input)) {
            $data['name'] = trim((string) $input['name']);
        }

        if (array_key_exists('email', $input)) {
            $data['email'] = strtolower(trim((string) $input['email']));
        }

        if (array_key_exists('phone', $input)) {
            $data['phone'] = Str::digits((string) $input['phone']);
        }

        if (array_key_exists('document', $input)) {
            $data['document'] = DocumentValidator::normalize((string) $input['document']);
        }

        if (array_key_exists('role', $input)) {
            $data['role'] = strtolower(trim((string) $input['role']));
        }

        if (array_key_exists('password', $input)) {
            $data['password'] = (string) $input['password'];
        }

        self::assertNormalized($data, false);

        if (!array_key_exists('password', $data) || $data['password'] === '') {
            unset($data['password']);
        }

        return $data;
    }

    /**
     * @param array<string, string> $data
     */
    private static function assertNormalized(array $data, bool $passwordRequired): void
    {
        if (($data['name'] ?? '') === '') {
            throw HttpException::validation('Name is required');
        }

        if (($data['email'] ?? '') === '' || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            throw HttpException::validation('A valid email is required');
        }

        if ($passwordRequired || (($data['password'] ?? '') !== '')) {
            if (strlen($data['password']) < 8) {
                throw HttpException::validation('Password must have at least 8 characters');
            }
        }

        $phoneDigits = Str::digits((string) ($data['phone'] ?? ''));
        if ($phoneDigits === '' || strlen($phoneDigits) < 10 || strlen($phoneDigits) > 15) {
            throw HttpException::validation('Phone must contain between 10 and 15 digits');
        }

        if (!DocumentValidator::isValid((string) ($data['document'] ?? ''))) {
            throw HttpException::validation('Document must be a valid CPF or CNPJ');
        }

        Role::assertValid((string) ($data['role'] ?? ''));
    }

    /**
     * @param array<string, mixed> $input
     * @return array<string, string>
     */
    private static function normalize(array $input): array
    {
        return [
            'name' => trim((string) ($input['name'] ?? '')),
            'email' => strtolower(trim((string) ($input['email'] ?? ''))),
            'password' => (string) ($input['password'] ?? ''),
            'phone' => Str::digits((string) ($input['phone'] ?? '')),
            'document' => DocumentValidator::normalize((string) ($input['document'] ?? '')),
            'role' => strtolower(trim((string) ($input['role'] ?? Role::USER))),
        ];
    }
}