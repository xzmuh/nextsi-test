<?php

declare(strict_types=1);

namespace App\Utils;

use App\Exceptions\HttpException;
use JsonException;

final class Json
{
    public static function decode(string $json): mixed
    {
        try {
            return json_decode($json, true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw HttpException::validation('Invalid JSON payload');
        }
    }
}