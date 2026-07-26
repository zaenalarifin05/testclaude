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

    /** @return list<array> */
    public static function semua(): array
    {
        return self::db()
            ->query('SELECT id, nama, email, role, created_at FROM admin_user ORDER BY nama')
            ->fetchAll();
    }

    public static function cari(int $id): ?array
    {
        $stmt = self::db()->prepare('SELECT id, nama, email, role, created_at FROM admin_user WHERE id = :id');
        $stmt->execute(['id' => $id]);

        return $stmt->fetch() ?: null;
    }

    public static function emailDipakai(string $email, ?int $kecualiId = null): bool
    {
        if ($kecualiId !== null) {
            $stmt = self::db()->prepare('SELECT 1 FROM admin_user WHERE email = :email AND id != :id');
            $stmt->execute(['email' => $email, 'id' => $kecualiId]);
        } else {
            $stmt = self::db()->prepare('SELECT 1 FROM admin_user WHERE email = :email');
            $stmt->execute(['email' => $email]);
        }

        return (bool) $stmt->fetchColumn();
    }

    /**
     * @param array{nama: string, email: string, password: string, role: string} $data
     */
    public static function buat(array $data): int
    {
        $stmt = self::db()->prepare(
            'INSERT INTO admin_user (nama, email, password_hash, role) VALUES (:nama, :email, :hash, :role)'
        );
        $stmt->execute([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'hash' => password_hash($data['password'], PASSWORD_DEFAULT),
            'role' => $data['role'],
        ]);

        return (int) self::db()->lastInsertId();
    }

    /**
     * @param array{nama: string, email: string, role: string} $data
     */
    public static function perbarui(int $id, array $data): void
    {
        $stmt = self::db()->prepare(
            'UPDATE admin_user SET nama = :nama, email = :email, role = :role WHERE id = :id'
        );
        $stmt->execute([
            'nama' => $data['nama'],
            'email' => $data['email'],
            'role' => $data['role'],
            'id' => $id,
        ]);
    }

    public static function gantiPassword(int $id, string $password): void
    {
        $stmt = self::db()->prepare('UPDATE admin_user SET password_hash = :hash WHERE id = :id');
        $stmt->execute(['hash' => password_hash($password, PASSWORD_DEFAULT), 'id' => $id]);
    }

    public static function hapus(int $id): void
    {
        $stmt = self::db()->prepare('DELETE FROM admin_user WHERE id = :id');
        $stmt->execute(['id' => $id]);
    }

    public static function jumlahSuperadmin(): int
    {
        return (int) self::db()
            ->query("SELECT COUNT(*) FROM admin_user WHERE role = 'superadmin'")
            ->fetchColumn();
    }
}
