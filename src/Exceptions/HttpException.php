<?php

declare(strict_types=1);

namespace App\Exceptions;

use RuntimeException;
use Throwable;

final class HttpException extends RuntimeException
{
    public function __construct(
        string $message,
        private int $statusCode,
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $code, $previous);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public static function badRequest(string $message = 'Bad request'): self
    {
        return new self($message, 400);
    }

    public static function unauthorized(string $message = 'Authentication failed'): self
    {
        return new self($message, 401);
    }

    public static function forbidden(string $message = 'Access denied'): self
    {
        return new self($message, 403);
    }

    public static function notFound(string $message = 'Resource not found'): self
    {
        return new self($message, 404);
    }

    public static function conflict(string $message = 'Conflict'): self
    {
        return new self($message, 409);
    }

    public static function validation(string $message = 'Validation failed'): self
    {
        return new self($message, 422);
    }
}