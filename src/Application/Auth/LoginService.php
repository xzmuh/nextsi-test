<?php

declare(strict_types=1);

namespace App\Application\Auth;

use App\Domain\User\UserEntity;
use App\Domain\User\UserRepositoryInterface;
use App\Exceptions\HttpException;
use App\Infrastructure\Validation\UserInputValidator;
use App\Security\JwtService;

final class LoginService
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private JwtService $jwtService,
    ) {
    }
    
    public function handle(array $payload): array
    {
        $credentials = UserInputValidator::validateLogin($payload);
        $user = $this->userRepository->findActiveByEmail($credentials['email']);

        if ($user === null || !password_verify($credentials['password'], (string) $user['password_hash'])) {
            throw HttpException::unauthorized('Invalid credentials');
        }

        $token = $this->jwtService->encode([
            'sub' => (int) $user['id'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
            'name' => (string) $user['name'],
            'jti' => bin2hex(random_bytes(16)), // evita tokens duplicados
        ]);

        return [
            'access_token' => $token,
            'token_type' => 'Bearer',
            'expires_in' => $this->jwtService->ttl(),
            'user' => UserEntity::public($user),
        ];
    }
}