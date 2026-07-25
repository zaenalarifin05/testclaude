<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Dokumen;
use App\Models\Gelombang;
use App\Models\Pendaftar;
use App\Models\Wawancara;

class AdminController
{
    public function index(): void
    {
        Auth::requireLogin();

        $gelombangFilter = $_GET['gelombang'] ?? 'all';
        $tahapFilter = $_GET['tahap'] ?? 'all';
        $cari = trim($_GET['cari'] ?? '');

        $daftar = Pendaftar::ringkasanUntukAdmin();

        if ($gelombangFilter !== 'all') {
            $daftar = array_filter($daftar, static fn (array $r): bool => $r['gelombang_kode'] === $gelombangFilter);
        }
        if ($tahapFilter !== 'all') {
            $daftar = array_filter($daftar, static fn (array $r): bool => $r['tahap'] === $tahapFilter);
        }
        if ($cari !== '') {
            $needle = mb_strtolower($cari);
            $daftar = array_filter($daftar, static function (array $r) use ($needle): bool {
                $haystack = mb_strtolower($r['nama_siswa'] . ' ' . $r['nomor_pendaftaran']);
                return str_contains($haystack, $needle);
            });
        }

        $gelombangList = Gelombang::semua();
        $title = 'Panel Admin PPDB';

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/admin/index.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }

    public function detail(string $id): void
    {
        Auth::requireLogin();

        $detail = Pendaftar::detail((int) $id);

        if ($detail === null) {
            http_response_code(404);
            echo 'Pendaftar tidak ditemukan.';
            return;
        }

        $tahap = Pendaftar::hitungTahap($detail);
        $title = 'Kelola Pendaftar — ' . $detail['pendaftar']['nomor_pendaftaran'];

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/admin/detail.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }

    public function verifikasiDokumen(string $id, string $jenis): void
    {
        Auth::requireLogin();

        $status = $_POST['status'] ?? '';
        $catatan = trim($_POST['catatan'] ?? '');

        if (in_array($status, ['terverifikasi', 'ditolak'], true)) {
            Dokumen::verifikasi((int) $id, $jenis, $status, $catatan !== '' ? $catatan : null);
        }

        header('Location: /admin/pendaftar/' . $id);
        exit;
    }

    public function jadwalkanWawancara(string $id): void
    {
        Auth::requireLogin();

        $detail = Pendaftar::detail((int) $id);

        if ($detail === null) {
            http_response_code(404);
            echo 'Pendaftar tidak ditemukan.';
            return;
        }

        $sudahBolehDijadwalkan = $detail['wawancara'] !== null || Pendaftar::hitungTahap($detail) === 'menunggu_jadwal';

        $tanggal = $_POST['tanggal'] ?? '';
        $jam = $_POST['jam'] ?? '';
        $lokasi = trim($_POST['lokasi'] ?? '');

        if ($sudahBolehDijadwalkan && $tanggal !== '' && $jam !== '' && $lokasi !== '') {
            Wawancara::jadwalkan((int) $id, $tanggal, $jam, $lokasi);
        }

        header('Location: /admin/pendaftar/' . $id);
        exit;
    }

    public function tetapkanHasil(string $id): void
    {
        Auth::requireLogin();

        $hasil = $_POST['hasil'] ?? '';

        if (in_array($hasil, ['diterima', 'tidak_diterima', 'cadangan'], true)) {
            Pendaftar::tetapkanHasil((int) $id, $hasil, (int) Auth::id());
        }

        header('Location: /admin/pendaftar/' . $id);
        exit;
    }

    public function konfirmasiLunas(string $id): void
    {
        Auth::requireLogin();

        Pendaftar::konfirmasiLunas((int) $id, (int) Auth::id());

        header('Location: /admin/pendaftar/' . $id);
        exit;
    }

    public function lihatDokumen(string $id, string $jenis): void
    {
        Auth::requireLogin();

        $dokumen = Dokumen::jenisValid($jenis) ? Dokumen::cari((int) $id, $jenis) : null;

        if ($dokumen === null || $dokumen['file_url'] === null) {
            http_response_code(404);
            echo 'Dokumen belum diunggah.';
            return;
        }

        $config = require dirname(__DIR__, 2) . '/config/config.php';
        $path = $config['upload_path'] . '/' . $dokumen['file_url'];

        if (!is_file($path)) {
            http_response_code(404);
            echo 'File tidak ditemukan di server.';
            return;
        }

        header('Content-Type: ' . (mime_content_type($path) ?: 'application/octet-stream'));
        header('Content-Disposition: inline; filename="' . basename($path) . '"');
        readfile($path);
    }
}
