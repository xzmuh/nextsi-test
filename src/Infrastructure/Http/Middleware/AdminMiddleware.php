<?php

declare(strict_types=1);

namespace App\Infrastructure\Http\Middleware;

use App\Exceptions\HttpException;
use App\Http\MiddlewareInterface;
use App\Http\Request;
use App\Http\Response;
use App\Security\AuthenticatedUser;

final class AdminMiddleware implements MiddlewareInterface
{
    public function handle(Request $request, callable $next): Response
    {
        $user = $request->attribute('authUser');

        if (!$user instanceof AuthenticatedUser || !$user->isAdmin()) {
            throw HttpException::forbidden('Admin role is required');
        }

        return $next($request);
    }
}