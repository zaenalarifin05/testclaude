<?php

namespace App\Controllers;

use App\Models\Dokumen;
use App\Models\Pendaftar;

class StatusController
{
    private const EKSTENSI_DIIZINKAN = ['pdf', 'jpg', 'jpeg', 'png'];
    private const MIME_DIIZINKAN = [
        'pdf' => 'application/pdf',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'png' => 'image/png',
    ];
    private const UKURAN_MAKS = 2 * 1024 * 1024; // 2MB

    public function form(): void
    {
        $this->render([], null, null);
    }

    public function cari(): void
    {
        $nomor = trim($_POST['nomor'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $errors = $this->validasiNomorNik($nomor, $nik);

        if (!empty($errors)) {
            http_response_code(422);
            $this->render($errors, $nomor, $nik);
            return;
        }

        $this->renderHasil($nomor, $nik);
    }

    public function unggahDokumen(string $jenis): void
    {
        $nomor = trim($_POST['nomor'] ?? '');
        $nik = trim($_POST['nik'] ?? '');
        $errors = $this->validasiNomorNik($nomor, $nik);

        if (!empty($errors) || !Dokumen::jenisValid($jenis)) {
            http_response_code(422);
            $this->render($errors ?: ['umum' => 'Permintaan tidak valid.'], $nomor, $nik);
            return;
        }

        $detail = Pendaftar::cariByNomorDanNik($nomor, $nik);

        if ($detail === null) {
            http_response_code(404);
            $this->render(
                ['umum' => 'Data tidak ditemukan. Periksa kembali nomor pendaftaran dan NIK Anda.'],
                $nomor,
                $nik
            );
            return;
        }

        $pesanUpload = $this->prosesUpload((int) $detail['pendaftar']['id'], $jenis);

        $this->renderHasil($nomor, $nik, $pesanUpload);
    }

    /** @return array<string, string> */
    private function validasiNomorNik(string $nomor, string $nik): array
    {
        $errors = [];

        if ($nomor === '') {
            $errors['nomor'] = 'Nomor pendaftaran wajib diisi.';
        }
        if (!preg_match('/^\d{16}$/', $nik)) {
            $errors['nik'] = 'NIK harus berupa 16 digit angka.';
        }

        return $errors;
    }

    private function prosesUpload(int $pendaftarId, string $jenis): array
    {
        $file = $_FILES['file'] ?? null;

        if ($file === null || $file['error'] === UPLOAD_ERR_NO_FILE) {
            return ['tipe' => 'danger', 'teks' => 'Pilih file terlebih dahulu.'];
        }
        if ($file['error'] !== UPLOAD_ERR_OK) {
            return ['tipe' => 'danger', 'teks' => 'Gagal mengunggah file. Coba lagi.'];
        }
        if ($file['size'] > self::UKURAN_MAKS) {
            return ['tipe' => 'danger', 'teks' => 'Ukuran file maksimal 2MB.'];
        }

        $ekstensi = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($ekstensi, self::EKSTENSI_DIIZINKAN, true)) {
            return ['tipe' => 'danger', 'teks' => 'Format file harus PDF, JPG, atau PNG.'];
        }

        $mimeAsli = (string) mime_content_type($file['tmp_name']);

        if ($mimeAsli !== self::MIME_DIIZINKAN[$ekstensi]) {
            return ['tipe' => 'danger', 'teks' => 'Isi file tidak sesuai dengan ekstensinya.'];
        }

        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $namaFile = sprintf('%d_%s_%s.%s', $pendaftarId, $jenis, bin2hex(random_bytes(8)), $ekstensi);
        $tujuan = $config['upload_path'] . '/' . $namaFile;

        $dokumenLama = Dokumen::cari($pendaftarId, $jenis);

        if (!move_uploaded_file($file['tmp_name'], $tujuan)) {
            return ['tipe' => 'danger', 'teks' => 'Gagal menyimpan file di server.'];
        }

        if ($dokumenLama !== null && $dokumenLama['file_url'] !== null) {
            @unlink($config['upload_path'] . '/' . $dokumenLama['file_url']);
        }

        Dokumen::simpanFile($pendaftarId, $jenis, $namaFile);

        return ['tipe' => 'success', 'teks' => 'Dokumen berhasil diunggah, menunggu verifikasi panitia.'];
    }

    private function renderHasil(string $nomor, string $nik, ?array $pesanUpload = null): void
    {
        $detail = Pendaftar::cariByNomorDanNik($nomor, $nik);

        if ($detail === null) {
            http_response_code(404);
            $this->render(
                ['umum' => 'Data tidak ditemukan. Periksa kembali nomor pendaftaran dan NIK Anda.'],
                $nomor,
                $nik
            );
            return;
        }

        $tahap = Pendaftar::hitungTahap($detail);
        $title = 'Status Pendaftaran';

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/status/hasil.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }

    private function render(array $errors, ?string $nomor, ?string $nik): void
    {
        $title = 'Cek Status Pendaftaran';

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/status/cek.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }
}
