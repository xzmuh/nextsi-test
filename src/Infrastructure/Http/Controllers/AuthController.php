<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Controllers;

use App\Application\Auth\LoginService;
use App\Http\Request;
use App\Http\Response;

final class AuthController
{
    public function __construct(private LoginService $loginService)
    {
    }

    public function login(Request $request): Response
    {
        return Response::json([
            'success' => true,
            'data' => $this->loginService->handle($request->json()),
        ]);
    }
}