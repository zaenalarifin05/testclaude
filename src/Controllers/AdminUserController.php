<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\AdminUser;

class AdminUserController
{
    private const ROLE = ['panitia', 'superadmin'];

    public function index(): void
    {
        Auth::requireSuperadmin();

        $daftar = AdminUser::semua();
        $title = 'Kelola Akun Admin';

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/admin/akun/index.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }

    public function baru(): void
    {
        Auth::requireSuperadmin();

        $this->renderForm(null, [], []);
    }

    public function simpan(): void
    {
        Auth::requireSuperadmin();

        [$data, $errors] = $this->validasi($_POST, null);

        if (!empty($errors)) {
            http_response_code(422);
            $this->renderForm(null, $errors, $_POST);
            return;
        }

        AdminUser::buat($data);

        header('Location: /admin/akun');
        exit;
    }

    public function edit(string $id): void
    {
        Auth::requireSuperadmin();

        $akun = AdminUser::cari((int) $id);

        if ($akun === null) {
            http_response_code(404);
            echo 'Akun tidak ditemukan.';
            return;
        }

        $this->renderForm($akun, [], $akun);
    }

    public function perbarui(string $id): void
    {
        Auth::requireSuperadmin();

        $akun = AdminUser::cari((int) $id);

        if ($akun === null) {
            http_response_code(404);
            echo 'Akun tidak ditemukan.';
            return;
        }

        [$data, $errors] = $this->validasi($_POST, (int) $id);

        if (!empty($errors)) {
            http_response_code(422);
            $this->renderForm($akun, $errors, $_POST);
            return;
        }

        if ($akun['role'] === 'superadmin' && $data['role'] !== 'superadmin' && AdminUser::jumlahSuperadmin() <= 1) {
            http_response_code(422);
            $this->renderForm($akun, ['role' => 'Tidak bisa menurunkan role — ini satu-satunya superadmin yang tersisa.'], $_POST);
            return;
        }

        AdminUser::perbarui((int) $id, $data);

        if (trim($_POST['password'] ?? '') !== '') {
            AdminUser::gantiPassword((int) $id, trim($_POST['password']));
        }

        header('Location: /admin/akun');
        exit;
    }

    public function hapus(string $id): void
    {
        Auth::requireSuperadmin();

        $akun = AdminUser::cari((int) $id);

        if ($akun === null) {
            header('Location: /admin/akun');
            exit;
        }

        if ((int) $id === Auth::id()) {
            header('Location: /admin/akun?error=diri_sendiri');
            exit;
        }

        if ($akun['role'] === 'superadmin' && AdminUser::jumlahSuperadmin() <= 1) {
            header('Location: /admin/akun?error=superadmin_terakhir');
            exit;
        }

        AdminUser::hapus((int) $id);

        header('Location: /admin/akun');
        exit;
    }

    /**
     * @return array{0: array{nama: string, email: string, password: string, role: string}, 1: array<string, string>}
     */
    private function validasi(array $input, ?int $idSaatIni): array
    {
        $errors = [];

        $data = [
            'nama' => trim($input['nama'] ?? ''),
            'email' => trim($input['email'] ?? ''),
            'password' => $input['password'] ?? '',
            'role' => $input['role'] ?? '',
        ];

        if ($data['nama'] === '') {
            $errors['nama'] = 'Nama wajib diisi.';
        }

        if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['email'] = 'Email tidak valid.';
        } elseif (AdminUser::emailDipakai($data['email'], $idSaatIni)) {
            $errors['email'] = 'Email ini sudah dipakai akun lain.';
        }

        if ($idSaatIni === null && strlen($data['password']) < 8) {
            $errors['password'] = 'Kata sandi minimal 8 karakter.';
        } elseif ($idSaatIni !== null && $data['password'] !== '' && strlen($data['password']) < 8) {
            $errors['password'] = 'Kata sandi minimal 8 karakter.';
        }

        if (!in_array($data['role'], self::ROLE, true)) {
            $errors['role'] = 'Pilih role yang valid.';
        }

        return [$data, $errors];
    }

    private function renderForm(?array $akun, array $errors, array $old): void
    {
        $title = $akun === null ? 'Tambah Akun Admin' : 'Edit Akun Admin — ' . $akun['nama'];

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/admin/akun/form.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }
}
