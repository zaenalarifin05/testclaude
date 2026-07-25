<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\AdminUser;

class AuthController
{
    public function form(): void
    {
        if (Auth::check()) {
            header('Location: /admin');
            exit;
        }

        $this->render([]);
    }

    public function login(): void
    {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        $admin = AdminUser::cariByEmail($email);

        if ($admin === null || !password_verify($password, $admin['password_hash'])) {
            http_response_code(422);
            $this->render(['umum' => 'Email atau kata sandi salah.']);
            return;
        }

        Auth::login((int) $admin['id']);
        header('Location: /admin');
        exit;
    }

    public function logout(): void
    {
        Auth::logout();
        header('Location: /admin/login');
        exit;
    }

    private function render(array $errors): void
    {
        $title = 'Login Admin';

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/admin/login.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }
}
