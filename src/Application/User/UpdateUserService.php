<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserEntity;
use App\Domain\User\UserRepositoryInterface;
use App\Exceptions\HttpException;
use App\Infrastructure\Validation\UserInputValidator;

final class UpdateUserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /**
     * @param array<string, mixed> $payload
     * @return array<string, mixed>
     */
    public function handle(int $id, array $payload): array
    {
        $current = $this->userRepository->findActiveById($id);
        if ($current === null) {
            throw HttpException::notFound('User not found');
        }

        $data = UserInputValidator::validateUpdate($payload, $current);

        $userWithSameEmail = $this->userRepository->findActiveByEmail($data['email']);
        if ($userWithSameEmail !== null && (int) $userWithSameEmail['id'] !== $id) {
            throw HttpException::conflict('Email already registered');
        }

        $userWithSameDocument = $this->userRepository->findActiveByDocument($data['document']);
        if ($userWithSameDocument !== null && (int) $userWithSameDocument['id'] !== $id) {
            throw HttpException::conflict('Document already registered');
        }

        $updated = $this->userRepository->update($id, [
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => array_key_exists('password', $data)
                ? password_hash($data['password'], PASSWORD_DEFAULT)
                : (string) $current['password_hash'],
            'phone' => $data['phone'],
            'document' => $data['document'],
            'role' => $data['role'],
        ]);

        return UserEntity::public($updated);
    }
}