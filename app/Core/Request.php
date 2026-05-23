<?php

declare(strict_types=1);

namespace App\Core;

final class Request
{
    public readonly string $method;
    public readonly string $uri;
    public readonly string $path;

    public function __construct()
    {
        $this->method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
        $this->uri    = $_SERVER['REQUEST_URI'] ?? '/';
        $this->path   = parse_url($this->uri, PHP_URL_PATH) ?? '/';
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_GET[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $_POST[$key] ?? $default;
    }

    public function isGet(): bool
    {
        return $this->method === 'GET';
    }

    public function isPost(): bool
    {
        return $this->method === 'POST';
    }

    public function getString(string $key, string $default = ''): string
    {
        return (string) ($this->get($key) ?? $default);
    }

    public function getInt(string $key, int $default = 0): int
    {
        $value = $this->get($key);
        return $value !== null ? (int) $value : $default;
    }
}