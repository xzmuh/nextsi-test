<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserEntity;
use App\Domain\User\UserRepositoryInterface;
use App\Exceptions\HttpException;

final class FindDeletedUserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /** @return array<string, mixed> */
    public function handle(int $id): array
    {
        $user = $this->userRepository->findDeletedById($id);
        if ($user === null) {
            throw HttpException::notFound('User not found');
        }

        return UserEntity::public($user);
    }
}