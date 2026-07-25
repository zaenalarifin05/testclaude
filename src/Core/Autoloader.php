<?php

namespace App\Core;

class Autoloader
{
    private const NAMESPACE_PREFIX = 'App\\';

    public static function register(): void
    {
        spl_autoload_register([self::class, 'load']);
    }

    private static function load(string $class): void
    {
        if (strncmp(self::NAMESPACE_PREFIX, $class, strlen(self::NAMESPACE_PREFIX)) !== 0) {
            return;
        }

        $relative = substr($class, strlen(self::NAMESPACE_PREFIX));
        $path = __DIR__ . '/../' . str_replace('\\', '/', $relative) . '.php';

        if (is_file($path)) {
            require $path;
        }
    }
}
