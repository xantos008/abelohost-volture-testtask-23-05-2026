<?php

declare(strict_types=1);

namespace App\Core;

final class Response
{
    public static function redirect(string $url, int $statusCode = 302): never
    {
        http_response_code($statusCode);
        header("Location: {$url}");
        exit;
    }

    public static function notFound(): never
    {
        http_response_code(404);
        // Рендер 404 будет делегирован роутером
        exit;
    }

    public static function setSecurityHeaders(array $config): void
    {
        header("X-Frame-Options: {$config['x_frame_options']}");
        header("X-Content-Type-Options: {$config['x_content_type_options']}");
        header("Referrer-Policy: {$config['referrer_policy']}");
        header("Content-Security-Policy: {$config['csp']}");
        // Убираем информацию о сервере
        header_remove('X-Powered-By');
    }
}