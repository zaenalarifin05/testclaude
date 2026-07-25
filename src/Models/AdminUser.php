<?php

namespace App\Models;

class AdminUser extends Model
{
    public static function cariByEmail(string $email): ?array
    {
        $stmt = self::db()->prepare('SELECT * FROM admin_user WHERE email = :email');
        $stmt->execute(['email' => $email]);

        return $stmt->fetch() ?: null;
    }
}
