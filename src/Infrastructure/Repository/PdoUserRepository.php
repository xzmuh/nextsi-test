<?php

declare(strict_types=1);

namespace App\Infrastructure\Repository;

use App\Domain\User\UserRepositoryInterface;
use PDO;

final class PdoUserRepository implements UserRepositoryInterface
{
    public function __construct(private PDO $connection)
    {
    }

    public function findAllActive(): array
    {
        $statement = $this->connection->query(
            'SELECT id, name, email, phone, document, role, created_at, updated_at
             FROM users
             WHERE deleted_at IS NULL
             ORDER BY id DESC'
        );

        return $statement->fetchAll();
    }

    public function findActiveById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, password_hash, phone, document, role, created_at, updated_at
             FROM users
             WHERE id = :id
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public function findDeletedById(int $id): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, password_hash, phone, document, role, created_at, updated_at, deleted_at, deleted_by
             FROM users
             WHERE id = :id
               AND deleted_at IS NOT NULL
             LIMIT 1'
        );
        $statement->execute(['id' => $id]);

        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public function findActiveByEmail(string $email): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, password_hash, phone, document, role, created_at, updated_at
             FROM users
             WHERE email = :email
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['email' => $email]);

        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public function findActiveByDocument(string $document): ?array
    {
        $statement = $this->connection->prepare(
            'SELECT id, name, email, password_hash, phone, document, role, created_at, updated_at
             FROM users
             WHERE document = :document
               AND deleted_at IS NULL
             LIMIT 1'
        );
        $statement->execute(['document' => $document]);

        $row = $statement->fetch();
        return $row === false ? null : $row;
    }

    public function create(array $data): array
    {
        $statement = $this->connection->prepare(
            'INSERT INTO users (name, email, password_hash, phone, document, role)
             VALUES (:name, :email, :password_hash, :phone, :document, :role)'
        );
        $statement->execute([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'phone' => $data['phone'],
            'document' => $data['document'],
            'role' => $data['role'],
        ]);

        return $this->findActiveById((int) $this->connection->lastInsertId()) ?? [];
    }

    public function update(int $id, array $data): array
    {
        $statement = $this->connection->prepare(
            'UPDATE users
             SET name = :name,
                 email = :email,
                 password_hash = :password_hash,
                 phone = :phone,
                 document = :document,
                 role = :role,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );
        $statement->execute([
            'id' => $id,
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => $data['password_hash'],
            'phone' => $data['phone'],
            'document' => $data['document'],
            'role' => $data['role'],
        ]);

        return $this->findActiveById($id) ?? [];
    }

    public function softDelete(int $id, int $deletedBy): void
    {
        $statement = $this->connection->prepare(
            'UPDATE users
             SET deleted_at = CURRENT_TIMESTAMP,
                 deleted_by = :deleted_by,
                 updated_at = CURRENT_TIMESTAMP
             WHERE id = :id
               AND deleted_at IS NULL'
        );
        $statement->execute([
            'id' => $id,
            'deleted_by' => $deletedBy,
        ]);
    }

    public function restore(int $id): void
    {
        $statement = $this->connection->prepare(
            'UPDATE users
             SET deleted_at = null,
                 deleted_by = null
             WHERE id = :id
               AND deleted_at IS NOT NULL'
        );
        $statement->execute([
            'id' => $id
        ]);
    }

    //SQL Injection evitado com PDO (prepare e execute)
}