<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var array<string, array<string, array{controller: string, action: string}>> */
    private array $routes = [];

    public function get(string $path, string $controller, string $action): void
    {
        $this->routes['GET'][$path] = compact('controller', 'action');
    }

    public function post(string $path, string $controller, string $action): void
    {
        $this->routes['POST'][$path] = compact('controller', 'action');
    }

    public function dispatch(Request $request): void
    {
        $method = $request->method;
        $path   = rtrim($request->path, '/') ?: '/';

        // Сначала пробуем точное совпадение
        if (isset($this->routes[$method][$path])) {
            $this->call($this->routes[$method][$path], $request, []);
            return;
        }

        // Затем ищем маршруты с параметрами вида {param}
        foreach ($this->routes[$method] ?? [] as $route => $handler) {
            $pattern = $this->toRegex($route);

            if (preg_match($pattern, $path, $matches)) {
                $params = array_filter(
                    $matches,
                    static fn($key) => !is_int($key),
                    ARRAY_FILTER_USE_KEY
                );
                $this->call($handler, $request, $params);
                return;
            }
        }

        $this->handleNotFound($request);
    }

    /**
     * @param array{controller: string, action: string} $handler
     * @param array<string, string> $params
     */
    private function call(array $handler, Request $request, array $params): void
    {
        $controllerClass = $handler['controller'];
        $action          = $handler['action'];

        if (!class_exists($controllerClass)) {
            $this->handleNotFound($request);
            return;
        }

        $controller = new $controllerClass();

        if (!method_exists($controller, $action)) {
            $this->handleNotFound($request);
            return;
        }

        $controller->$action($request, $params);
    }

    private function toRegex(string $route): string
    {
        // Преобразует /post/{slug} → именованные группы regex
        $pattern = preg_replace('/\{([a-zA-Z_]+)\}/', '(?P<$1>[^/]+)', $route);
        return '#^' . $pattern . '$#';
    }

    private function handleNotFound(Request $request): never
    {
        http_response_code(404);

        $smarty = $GLOBALS['smarty'] ?? null;

        if ($smarty !== null) {
            $smarty->display('pages/error.tpl');
        } else {
            echo '<h1>404 Not Found</h1>';
        }

        exit;
    }
}