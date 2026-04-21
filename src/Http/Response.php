<?php

declare(strict_types=1);

namespace App\Http;

final class Response
{
    public function __construct(
        private int $status,
        private string $body,
        private array $headers = []
    ) {
    }

    public static function json(array $payload, int $status = 200, array $headers = []): self
    {
        return new self(
            $status,
            json_encode($payload, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR),
            array_merge([
                'Content-Type' => 'application/json; charset=utf-8',
                'X-Content-Type-Options' => 'nosniff',
                'X-Frame-Options' => 'DENY',
                'Referrer-Policy' => 'no-referrer',
            ], $headers)
        );
    }

    public function send(): void
    {
        http_response_code($this->status);

        foreach ($this->headers as $name => $value) {
            header($name . ': ' . $value);
        }

        echo $this->body;
    }
}