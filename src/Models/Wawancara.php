<?php

namespace App\Models;

class Wawancara extends Model
{
    public static function jadwalkan(int $pendaftarId, string $tanggal, string $jam, string $lokasi): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO wawancara (pendaftar_id, tanggal, jam, lokasi)
             VALUES (:pendaftar_id, :tanggal, :jam, :lokasi)
             ON DUPLICATE KEY UPDATE tanggal = VALUES(tanggal), jam = VALUES(jam), lokasi = VALUES(lokasi)'
        );
        $stmt->execute([
            'pendaftar_id' => $pendaftarId,
            'tanggal' => $tanggal,
            'jam' => $jam,
            'lokasi' => $lokasi,
        ]);
    }
}
