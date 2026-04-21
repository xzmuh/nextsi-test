<?php

declare(strict_types=1);

namespace App\Domain\User;

use App\Exceptions\HttpException;

final class Role
{
    public const ADMIN = 'admin';
    public const USER = 'user';

    public static function assertValid(string $role): void
    {
        if (!in_array($role, [self::ADMIN, self::USER], true)) {
            throw HttpException::badRequest('Role must be admin or user');
        }
    }
}