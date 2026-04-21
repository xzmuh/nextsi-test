<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Domain\User\UserRepositoryInterface;
use App\Exceptions\HttpException;
use App\Http\MiddlewareInterface;
use App\Http\Request;
use App\Http\Response;
use App\Security\AuthenticatedUser;
use App\Security\JwtService;

final class AuthMiddleware implements MiddlewareInterface
{
    public function __construct(
        private JwtService $jwtService,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();

        if ($token === null) {
            throw HttpException::unauthorized('Bearer token is required');
        }

        $payload = $this->jwtService->decode($token);
        $userId = (int) ($payload['sub'] ?? 0);

        if ($userId <= 0) {
            throw HttpException::unauthorized('Invalid token subject');
        }

        $user = $this->userRepository->findActiveById($userId);

        if ($user === null) {
            throw HttpException::unauthorized('Authenticated user no longer exists');
        }

        return $next($request->withAttribute('authUser', new AuthenticatedUser(
            (int) $user['id'],
            (string) $user['name'],
            (string) $user['email'],
            (string) $user['role'],
        )));
    }
}