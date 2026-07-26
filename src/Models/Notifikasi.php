<?php

namespace App\Models;

class Notifikasi extends Model
{
    public static function catat(int $pendaftarId, string $judul, string $pesan, string $channel = 'email'): void
    {
        $stmt = self::db()->prepare(
            'INSERT INTO notifikasi (pendaftar_id, judul, pesan, channel) VALUES (:pendaftar_id, :judul, :pesan, :channel)'
        );
        $stmt->execute([
            'pendaftar_id' => $pendaftarId,
            'judul' => $judul,
            'pesan' => $pesan,
            'channel' => $channel,
        ]);
    }

    /** @return list<array> */
    public static function untukPendaftar(int $pendaftarId): array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM notifikasi WHERE pendaftar_id = :pendaftar_id ORDER BY dikirim_at DESC'
        );
        $stmt->execute(['pendaftar_id' => $pendaftarId]);

        return $stmt->fetchAll();
    }
}
