<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserEntity;
use App\Domain\User\UserRepositoryInterface;
use App\Exceptions\HttpException;

final class FindUserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function handle(int $id): array
    {
        $user = $this->userRepository->findActiveById($id);
        if ($user === null) {
            throw HttpException::notFound('User not found');
        }

        return UserEntity::public($user);
    }
}