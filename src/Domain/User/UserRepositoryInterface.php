<?php

declare(strict_types=1);

namespace App\Domain\User;

interface UserRepositoryInterface
{
    public function findAllActive(): array;
    public function findActiveById(int $id): ?array;
    public function findDeletedById(int $id): ?array;
    public function findActiveByEmail(string $email): ?array;
    public function findActiveByDocument(string $document): ?array;
    public function create(array $data): array;
    public function update(int $id, array $data): array;
    public function softDelete(int $id, int $deletedBy): void;
    public function restore(int $id): void;
}