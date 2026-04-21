<?php

declare(strict_types=1);

namespace App\Security;

use App\Domain\User\Role;

final class AuthenticatedUser
{
    public function __construct(
        public int $id,
        public string $name,
        public string $email,
        public string $role,
    ) {
    }

    public function isAdmin(): bool
    {
        return $this->role === Role::ADMIN;
    }
}