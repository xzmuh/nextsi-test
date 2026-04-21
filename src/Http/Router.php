<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;

final class Router
{
    /**
     * @var list<array{method:string, pattern:string, regex:string, handler:callable, middlewares:list<MiddlewareInterface>}>
     */
    private array $routes = [];

    /**
     * @param callable(Request): Response $handler
     * @param list<MiddlewareInterface> $middlewares
     */
    public function add(string $method, string $pattern, callable $handler, array $middlewares = []): void
    {
        $normalizedPattern = '/' . trim($pattern, '/');
        if ($normalizedPattern === '//') {
            $normalizedPattern = '/';
        }

        $regex = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $normalizedPattern);
        $regex = '#^' . $regex . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'pattern' => $normalizedPattern,
            'regex' => $regex,
            'handler' => $handler,
            'middlewares' => $middlewares,
        ];
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($route['method'] !== $request->method()) {
                continue;
            }

            if (preg_match($route['regex'], $request->path(), $matches) !== 1) {
                continue;
            }

            $resolvedRequest = $request;
            foreach ($matches as $key => $value) {
                if (!is_string($key)) {
                    continue;
                }

                $resolvedRequest = $resolvedRequest->withAttribute($key, $value);
            }

            $handler = $route['handler'];
            $pipeline = array_reduce(
                array_reverse($route['middlewares']),
                /** @param callable(Request): Response $next */
                static fn (callable $next, MiddlewareInterface $middleware): callable =>
                    static fn (Request $request): Response => $middleware->handle($request, $next),
                static fn (Request $request): Response => $handler($request)
            );

            return $pipeline($resolvedRequest);
        }

        throw HttpException::notFound('Route not found');
    }
}