<?php

declare(strict_types=1);

namespace App\Application\User;

use App\Domain\User\UserEntity;
use App\Domain\User\UserRepositoryInterface;

final class ListUsersService
{
    public function __construct(private UserRepositoryInterface $userRepository)
    {
    }

    /** @return list<array<string, mixed>> */
    public function handle(): array
    {
        return array_map(
            static fn (array $row): array => UserEntity::public($row),
            $this->userRepository->findAllActive()
        );
    }
}