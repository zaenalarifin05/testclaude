<?php

namespace App\Core;

class Router
{
    /** @var array<string, list<array{0: string, 1: array{0: class-string, 1: string}}>> */
    private array $routes = ['GET' => [], 'POST' => []];

    public function get(string $path, array $handler): void
    {
        $this->routes['GET'][] = [$path, $handler];
    }

    public function post(string $path, array $handler): void
    {
        $this->routes['POST'][] = [$path, $handler];
    }

    public function dispatch(string $method, string $uri): void
    {
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';

        foreach ($this->routes[$method] ?? [] as [$pattern, $handler]) {
            $params = $this->match($pattern, $path);

            if ($params !== null) {
                [$controllerClass, $action] = $handler;
                $controller = new $controllerClass();
                $controller->$action(...$params);
                return;
            }
        }

        http_response_code(404);
        echo '404 - Halaman tidak ditemukan';
    }

    /**
     * Mencocokkan path terhadap pattern yang boleh berisi segmen dinamis
     * seperti "/admin/pendaftar/{id}". Mengembalikan null kalau tidak cocok.
     *
     * @return list<string>|null
     */
    private function match(string $pattern, string $path): ?array
    {
        $regex = preg_replace('#\{[a-zA-Z_]+\}#', '([^/]+)', $pattern);

        if (preg_match('#^' . $regex . '$#', $path, $matches)) {
            array_shift($matches);
            return $matches;
        }

        return null;
    }
}
