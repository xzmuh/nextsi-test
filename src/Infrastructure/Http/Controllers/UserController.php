<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\User\CreateUserService;
use App\Application\User\DeleteUserService;
use App\Application\User\FindUserService;
use App\Application\User\ListUsersService;
use App\Application\User\UpdateUserService;
use App\Application\User\RestoreUserService;
use App\Application\User\FindDeletedUserService;
use App\Http\Request;
use App\Http\Response;
use App\Security\AuthenticatedUser;

final class UserController
{
    public function __construct(
        private CreateUserService $createUserService,
        private ListUsersService $listUsersService,
        private FindUserService $findUserService,
        private FindDeletedUserService $findDeletedUserService,
        private UpdateUserService $updateUserService,
        private DeleteUserService $deleteUserService,
        private RestoreUserService $restoreUserService,
    ) {
    }

    public function index(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'data' => $this->listUsersService->handle(),
        ]);
    }

    public function show(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'data' => $this->findUserService->handle((int) $request->attribute('id')),
        ]);
    }

    public function showDeleted(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'data' => $this->findDeletedUserService->handle((int) $request->attribute('id')),
        ]);
    }

    public function store(Request $request): Response
    {
        $user = $this->createUserService->handle($request->json());

        return Response::json([
            'success' => true,
            'message' => 'User created successfully',
            'data' => $user,
        ], 201);
    }

    public function update(Request $request): Response
    {
        $user = $this->updateUserService->handle(
            (int) $request->attribute('id'),
            $request->json()
        );

        return Response::json([
            'success' => true,
            'message' => 'User updated successfully',
            'data' => $user,
        ]);
    }

    public function restore(Request $request): Response
    {
        $user = $this->restoreUserService->handle((int) $request->attribute('id'));

        return Response::json([
            'success' => true,
            'message' => 'User restored successfully',
            'data' => $user,
        ]);
    }

    public function destroy(Request $request): Response
    {
        $authUser = $request->attribute('authUser');

        if (!$authUser instanceof AuthenticatedUser) {
            throw new \LogicException('Authenticated user must be present');
        }

        $this->deleteUserService->handle((int) $request->attribute('id'), $authUser->id);

        return Response::json([
            'success' => true,
            'message' => 'User deleted successfully',
            'data' => null,
        ]);
    }
}