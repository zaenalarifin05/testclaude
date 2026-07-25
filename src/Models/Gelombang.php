<?php

namespace App\Models;

class Gelombang extends Model
{
    public static function aktif(): ?array
    {
        $stmt = self::db()->query(
            'SELECT * FROM gelombang WHERE aktif = 1 ORDER BY urutan ASC LIMIT 1'
        );

        return $stmt->fetch() ?: null;
    }

    public static function semua(): array
    {
        return self::db()->query('SELECT * FROM gelombang ORDER BY urutan ASC')->fetchAll();
    }
}
