<?php

declare(strict_types=1);

namespace App\Domain\User;

final class UserEntity
{
    public static function public(array $row): array
    {
        return [
            'id' => (int) $row['id'],
            'name' => (string) $row['name'],
            'email' => (string) $row['email'],
            'phone' => (string) $row['phone'],
            'document' => (string) $row['document'],
            'role' => (string) $row['role'],
            'created_at' => $row['created_at'] ?? null,
            'updated_at' => $row['updated_at'] ?? null,
            'deleted_at' => $row['deleted_at'] ?? null,
            'deleted_by' => $row['deleted_by'] ?? null,
        ];
    }
}