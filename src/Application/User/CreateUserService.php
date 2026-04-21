<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserEntity;
use App\Domain\User\UserRepositoryInterface;
use App\Exceptions\HttpException;
use App\Infrastructure\Validation\UserInputValidator;

final class CreateUserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function handle(array $payload): array
    {
        $data = UserInputValidator::validateCreate($payload);

        if ($this->userRepository->findActiveByEmail($data['email']) !== null) {
            throw HttpException::conflict('Email already registered');
        }

        if ($this->userRepository->findActiveByDocument($data['document']) !== null) {
            throw HttpException::conflict('Document already registered');
        }

        $user = $this->userRepository->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password_hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'phone' => $data['phone'],
            'document' => $data['document'],
            'role' => $data['role'],
        ]);

        return UserEntity::public($user);
    }
}