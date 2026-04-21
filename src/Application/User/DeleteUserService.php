<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserRepositoryInterface;
use App\Exceptions\HttpException;

final class DeleteUserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function handle(int $id, int $deletedBy): void
    {
        $user = $this->userRepository->findActiveById($id);
        if ($user === null) {
            throw HttpException::notFound('User not found');
        }

        $this->userRepository->softDelete($id, $deletedBy);
    }
}