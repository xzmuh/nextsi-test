<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserRepositoryInterface;
use App\Exceptions\HttpException;

final class RestoreUserService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    public function handle(int $id): void
    {
        $user = $this->userRepository->findDeletedById($id);
        if ($user === null) {
            throw HttpException::notFound('User not found');
        }

        $this->userRepository->restore($id);
    }
}