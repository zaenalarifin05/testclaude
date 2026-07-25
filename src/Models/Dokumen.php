<?php

namespace App\Models;

use PDO;

class Dokumen extends Model
{
    private const JENIS = ['kk', 'akta', 'ijazah', 'foto'];

    public static function initUntukPendaftar(int $pendaftarId, ?PDO $db = null): void
    {
        $db ??= self::db();

        $stmt = $db->prepare(
            'INSERT INTO dokumen (pendaftar_id, jenis, status) VALUES (:pendaftar_id, :jenis, "belum_upload")'
        );

        foreach (self::JENIS as $jenis) {
            $stmt->execute(['pendaftar_id' => $pendaftarId, 'jenis' => $jenis]);
        }
    }

    public static function verifikasi(int $pendaftarId, string $jenis, string $status, ?string $catatan): bool
    {
        if (!in_array($jenis, self::JENIS, true) || !in_array($status, ['terverifikasi', 'ditolak'], true)) {
            return false;
        }

        $stmt = self::db()->prepare(
            'UPDATE dokumen
             SET status = :status, catatan_verifikasi = :catatan, verified_at = NOW()
             WHERE pendaftar_id = :pendaftar_id AND jenis = :jenis'
        );

        return $stmt->execute([
            'status' => $status,
            'catatan' => $catatan,
            'pendaftar_id' => $pendaftarId,
            'jenis' => $jenis,
        ]);
    }

    public static function jenisValid(string $jenis): bool
    {
        return in_array($jenis, self::JENIS, true);
    }

    public static function cari(int $pendaftarId, string $jenis): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT * FROM dokumen WHERE pendaftar_id = :pendaftar_id AND jenis = :jenis'
        );
        $stmt->execute(['pendaftar_id' => $pendaftarId, 'jenis' => $jenis]);

        return $stmt->fetch() ?: null;
    }

    /**
     * Simpan file yang baru diunggah — mereset status ke menunggu_verifikasi
     * dan menghapus catatan penolakan sebelumnya (kalau ada).
     */
    public static function simpanFile(int $pendaftarId, string $jenis, string $namaFile): bool
    {
        $stmt = self::db()->prepare(
            'UPDATE dokumen
             SET file_url = :file_url, status = "menunggu_verifikasi", catatan_verifikasi = NULL,
                 uploaded_at = NOW(), verified_at = NULL
             WHERE pendaftar_id = :pendaftar_id AND jenis = :jenis'
        );

        return $stmt->execute([
            'file_url' => $namaFile,
            'pendaftar_id' => $pendaftarId,
            'jenis' => $jenis,
        ]);
    }
}
