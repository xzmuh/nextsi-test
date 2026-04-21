<?php

declare(strict_types=1);

namespace App\Http;

use App\Exceptions\HttpException;
use App\Support\Json;

final class Request
{
    /**
     * @param array<string, string> $headers
     * @param array<string, mixed> $query
     * @param array<string, mixed> $attributes
     */
    public function __construct(
        private string $method,
        private string $path,
        private array $headers,
        private array $query,
        private string $rawBody,
        private array $attributes = [],
    ) {
    }

    public static function capture(): self
    {
        $uriPath = (string) parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
        $path = self::normalizePath($uriPath);

        return new self(
            strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET'),
            $path,
            self::captureHeaders(),
            $_GET,
            file_get_contents('php://input') ?: ''
        );
    }

    private static function normalizePath(string $uriPath): string
    {
        $path = $uriPath !== '' ? $uriPath : '/';
        $path = str_replace('\\', '/', $path);

        $scriptName = str_replace('\\', '/', $_SERVER['SCRIPT_NAME'] ?? '');
        $scriptDir = rtrim(dirname($scriptName), '/');

        $prefixes = array_values(array_filter(array_unique([
            $scriptName !== '' ? $scriptName : null,
            $scriptDir !== '' && $scriptDir !== '.' ? $scriptDir : null,
        ])));

        usort($prefixes, static fn (string $a, string $b): int => strlen($b) <=> strlen($a));

        foreach ($prefixes as $prefix) {
            if ($prefix !== '/' && str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix)) ?: '/';
                break;
            }
        }

        $path = '/' . trim($path, '/');

        return $path === '//' ? '/' : $path;
    }

    /**
     * @return array<string, string>
     */
    private static function captureHeaders(): array
    {
        $headers = [];

        if (function_exists('getallheaders')) {
            foreach (getallheaders() as $name => $value) {
                if (is_string($name) && is_string($value)) {
                    $headers[self::normalizeHeaderName($name)] = trim($value);
                }
            }
        }

        if (function_exists('apache_request_headers')) {
            foreach (apache_request_headers() as $name => $value) {
                if (is_string($name) && is_string($value)) {
                    $headers[self::normalizeHeaderName($name)] = trim($value);
                }
            }
        }

        foreach ($_SERVER as $key => $value) {
            if (!is_string($value)) {
                continue;
            }

            if (str_starts_with($key, 'HTTP_')) {
                $name = self::normalizeHeaderName(substr($key, 5));
                $headers[$name] = trim($value);
            }
        }

        foreach ([
            'HTTP_AUTHORIZATION' => 'Authorization',
            'REDIRECT_HTTP_AUTHORIZATION' => 'Authorization',
            'AUTHORIZATION' => 'Authorization',
            'CONTENT_TYPE' => 'Content-Type',
            'CONTENT_LENGTH' => 'Content-Length',
        ] as $serverKey => $headerName) {
            if (isset($_SERVER[$serverKey]) && is_string($_SERVER[$serverKey]) && trim($_SERVER[$serverKey]) !== '') {
                $headers[$headerName] = trim($_SERVER[$serverKey]);
            }
        }

        return $headers;
    }

    private static function normalizeHeaderName(string $name): string
    {
        $name = str_replace('_', ' ', strtolower($name));
        $name = ucwords($name);
        return str_replace(' ', '-', $name);
    }

    public function method(): string
    {
        return $this->method;
    }

    public function path(): string
    {
        return $this->path;
    }

    public function header(string $name): ?string
    {
        $normalized = self::normalizeHeaderName($name);
        return $this->headers[$normalized] ?? null;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('Authorization');

        if ($authorization === null || trim($authorization) === '') {
            return null;
        }

        if (preg_match('/^\s*Bearer\s+(.+?)\s*$/i', $authorization, $matches) !== 1) {
            return null;
        }

        $token = trim($matches[1]);

        return $token !== '' ? $token : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function json(): array
    {
        if ($this->rawBody === '') {
            return [];
        }

        $decoded = Json::decode($this->rawBody);
        if (!is_array($decoded)) {
            throw HttpException::validation('JSON body must be an object');
        }

        return $decoded;
    }

    public function attribute(string $key, mixed $default = null): mixed
    {
        return $this->attributes[$key] ?? $default;
    }

    public function withAttribute(string $key, mixed $value): self
    {
        $clone = clone $this;
        $clone->attributes[$key] = $value;
        return $clone;
    }
}