<?php

namespace App\Models;

class Sekolah extends Model
{
    public static function ambil(): ?array
    {
        $stmt = self::db()->query('SELECT * FROM sekolah WHERE id = 1');

        return $stmt->fetch() ?: null;
    }
}
