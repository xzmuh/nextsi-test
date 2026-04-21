<?php

declare(strict_types=1);

use App\Application\Auth\LoginService;
use App\Application\User\CreateUserService;
use App\Application\User\DeleteUserService;
use App\Application\User\FindUserService;
use App\Application\User\ListUsersService;
use App\Application\User\UpdateUserService;
use App\Application\User\RestoreUserService;
use App\Application\User\FindDeletedUserService;
use App\Exceptions\HttpException;
use App\Http\Request;
use App\Http\Response;
use App\Http\Router;
use App\Infrastructure\Database\Connection;
use App\Infrastructure\Http\Controllers\AuthController;
use App\Infrastructure\Http\Controllers\UserController;
use App\Infrastructure\Http\Middleware\AdminMiddleware;
use App\Infrastructure\Http\Middleware\AuthMiddleware;
use App\Infrastructure\Repository\PdoUserRepository;
use App\Security\JwtService;

$basePath = dirname(__DIR__);
require $basePath . '/bootstrap.php';

date_default_timezone_set($_ENV['APP_TIMEZONE'] ?? 'America/Sao_Paulo');

try {
    $request = Request::capture();

    $router = new Router();
    $router->add('GET', '/health', static fn (): Response => Response::json([
        'success' => true,
        'data' => [
            'status' => 'ok',
            'timestamp' => gmdate(DATE_ATOM),
        ],
    ]));

    if ($request->method() === 'GET' && $request->path() === '/health') {
        $router->dispatch($request)->send();
        exit;
    }

    $connection = Connection::fromEnv($_ENV);
    $userRepository = new PdoUserRepository($connection);
    $jwtService = new JwtService(
        $_ENV['APP_KEY'] ?? '',
        (int) ($_ENV['JWT_TTL'] ?? 3600),
        ($_ENV['APP_URL'] ?? '') !== '' ? ($_ENV['APP_URL'] ?? null) : null
    );

    $authController = new AuthController(new LoginService($userRepository, $jwtService));
    $userController = new UserController(
        new CreateUserService($userRepository),
        new ListUsersService($userRepository),
        new FindUserService($userRepository),
        new FindDeletedUserService($userRepository),
        new UpdateUserService($userRepository),
        new DeleteUserService($userRepository),
        new RestoreUserService($userRepository),
    );

    $authMiddleware = new AuthMiddleware($jwtService, $userRepository);
    $adminMiddleware = new AdminMiddleware();

    $router->add('POST', '/auth/login', static fn (Request $request): Response => $authController->login($request));
    $router->add('GET', '/users', static fn (Request $request): Response => $userController->index($request), [$authMiddleware]);
    $router->add('GET', '/users/{id}', static fn (Request $request): Response => $userController->show($request), [$authMiddleware]);
    $router->add('GET', '/users/deleted/{id}', static fn (Request $request): Response => $userController->showDeleted($request), [$authMiddleware, $adminMiddleware]);
    $router->add('POST', '/users', static fn (Request $request): Response => $userController->store($request), [$authMiddleware, $adminMiddleware]);
    $router->add('PUT', '/users/{id}', static fn (Request $request): Response => $userController->update($request), [$authMiddleware, $adminMiddleware]);
    $router->add('PUT', '/users/restore/{id}', static fn (Request $request): Response => $userController->restore($request), [$authMiddleware, $adminMiddleware]);
    $router->add('DELETE', '/users/{id}', static fn (Request $request): Response => $userController->destroy($request), [$authMiddleware, $adminMiddleware]);

    $router->dispatch($request)->send();
} catch (HttpException $exception) {
    Response::json([
        'success' => false,
        'error' => [
            'message' => $exception->getMessage(),
            'code' => $exception->getCode() ?: $exception->statusCode(),
        ],
    ], $exception->statusCode())->send();
} catch (Throwable $exception) {
    Response::json([
        'success' => false,
        'error' => [
            'message' => ($_ENV['APP_DEBUG'] ?? 'false') === 'true'
                ? $exception->getMessage()
                : 'Internal Server Error',
        ],
    ], 500)->send();
}
