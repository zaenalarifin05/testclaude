<?php

namespace App\Core;

class Auth
{
    private static function start(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function login(int $adminId): void
    {
        self::start();
        session_regenerate_id(true);
        $_SESSION['admin_id'] = $adminId;
    }

    public static function logout(): void
    {
        self::start();
        unset($_SESSION['admin_id']);
        session_regenerate_id(true);
    }

    public static function id(): ?int
    {
        self::start();

        return $_SESSION['admin_id'] ?? null;
    }

    public static function check(): bool
    {
        return self::id() !== null;
    }

    public static function requireLogin(): void
    {
        if (!self::check()) {
            header('Location: /admin/login');
            exit;
        }
    }
}
