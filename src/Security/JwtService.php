<?php

declare(strict_types=1);

namespace App\Security;

use App\Exceptions\HttpException;
use App\Utils\Json;
use JsonException;

final class JwtService
{
    public function __construct(
        private string $secret,
        private int $ttl,
        private ?string $issuer = null,
    ) {
        if (trim($this->secret) === '') {
            throw new \RuntimeException('APP_KEY cannot be empty');
        }
    }

    public function ttl(): int
    {
        return $this->ttl;
    }

    public function encode(array $claims): string
    {
        $now = time();

        $header = [
            'alg' => 'HS256',
            'typ' => 'JWT',
        ];

        $payload = array_merge([
            'iat' => $now,
            'nbf' => $now,
            'exp' => $now + $this->ttl,
        ], $claims);

        if ($this->issuer !== null && !isset($payload['iss'])) {
            $payload['iss'] = $this->issuer;
        }

        $encodedHeader = $this->base64UrlEncode(json_encode($header, JSON_THROW_ON_ERROR));
        $encodedPayload = $this->base64UrlEncode(json_encode($payload, JSON_THROW_ON_ERROR));
        $encodedSignature = $this->sign($encodedHeader . '.' . $encodedPayload);

        return implode('.', [$encodedHeader, $encodedPayload, $encodedSignature]);
    }

    public function decode(string $token): array
    {
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            throw HttpException::unauthorized('Invalid token format');
        }

        [$encodedHeader, $encodedPayload, $encodedSignature] = $parts;

        $header = Json::decode($this->base64UrlDecode($encodedHeader));
        if (!is_array($header) || ($header['alg'] ?? null) !== 'HS256') {
            throw HttpException::unauthorized('Invalid token algorithm');
        }

        $expectedSignature = $this->sign($encodedHeader . '.' . $encodedPayload);
        if (!hash_equals($expectedSignature, $encodedSignature)) {
            throw HttpException::unauthorized('Invalid token signature');
        }

        $payload = Json::decode($this->base64UrlDecode($encodedPayload));
        if (!is_array($payload)) {
            throw HttpException::unauthorized('Invalid token payload');
        }

        $now = time();
        if (($payload['exp'] ?? 0) < $now) {
            throw HttpException::unauthorized('Token expired');
        }

        if (($payload['nbf'] ?? 0) > $now) {
            throw HttpException::unauthorized('Token not active');
        }

        if ($this->issuer !== null && ($payload['iss'] ?? null) !== $this->issuer) {
            throw HttpException::unauthorized('Invalid token issuer');
        }

        return $payload;
    }

    private function sign(string $input): string
    {
        return $this->base64UrlEncode(hash_hmac('sha256', $input, $this->secret, true));
    }

    private function base64UrlEncode(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }

    private function base64UrlDecode(string $value): string
    {
        $remainder = strlen($value) % 4;
        if ($remainder > 0) {
            $value .= str_repeat('=', 4 - $remainder);
        }

        $decoded = base64_decode(strtr($value, '-_', '+/'), true);
        if ($decoded === false) {
            throw HttpException::unauthorized('Invalid token encoding');
        }

        return $decoded;
    }
}