<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Models\Dokumen;
use App\Models\Gelombang;
use App\Models\Notifikasi;
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
        $notifikasi = Notifikasi::untukPendaftar((int) $id);
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

            $detail = Pendaftar::detail((int) $id);

            if ($detail !== null) {
                $label = dokumen_label($jenis);
                $nama = $detail['calon_siswa']['nama'];

                $pesan = $status === 'terverifikasi'
                    ? 'Dokumen ' . $label . ' milik ' . $nama . ' telah diverifikasi.'
                    : 'Dokumen ' . $label . ' milik ' . $nama . ' ditolak.'
                        . ($catatan !== '' ? ' Catatan: ' . $catatan . '.' : '') . ' Silakan unggah ulang.';

                Notifikasi::catat((int) $id, 'Verifikasi Dokumen — ' . $label, $pesan);
            }
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

            Notifikasi::catat(
                (int) $id,
                'Jadwal Wawancara & Seleksi',
                'Wawancara ' . $detail['calon_siswa']['nama'] . ' dijadwalkan pada ' . $tanggal
                    . ' pukul ' . $jam . ' di ' . $lokasi . '.'
            );
        }

        header('Location: /admin/pendaftar/' . $id);
        exit;
    }

    public function tetapkanHasil(string $id): void
    {
        Auth::requireLogin();

        $hasil = $_POST['hasil'] ?? '';

        if (in_array($hasil, ['diterima', 'tidak_diterima', 'cadangan'], true)) {
            $jumlahTagihan = Pendaftar::tetapkanHasil((int) $id, $hasil, (int) Auth::id());

            $detail = Pendaftar::detail((int) $id);

            if ($detail !== null) {
                $nama = $detail['calon_siswa']['nama'];

                $pesan = match ($hasil) {
                    'diterima' => 'Selamat! ' . $nama . ' dinyatakan DITERIMA. Silakan lakukan pembayaran PPDB'
                        . ' sebesar Rp' . number_format($jumlahTagihan ?? 0, 0, ',', '.') . '.',
                    'tidak_diterima' => 'Mohon maaf, ' . $nama . ' dinyatakan tidak diterima pada seleksi PPDB kali ini.',
                    default => $nama . ' dinyatakan sebagai peserta cadangan PPDB.',
                };

                Notifikasi::catat((int) $id, 'Hasil Seleksi PPDB', $pesan);
            }
        }

        header('Location: /admin/pendaftar/' . $id);
        exit;
    }

    public function konfirmasiLunas(string $id): void
    {
        Auth::requireLogin();

        $detail = Pendaftar::detail((int) $id);

        Pendaftar::konfirmasiLunas((int) $id, (int) Auth::id());

        if ($detail !== null && $detail['pembayaran'] !== null) {
            $jumlah = number_format((float) $detail['pembayaran']['jumlah_tagihan'], 0, ',', '.');

            Notifikasi::catat(
                (int) $id,
                'Konfirmasi Pembayaran Diterima',
                'Pembayaran PPDB sebesar Rp' . $jumlah . ' untuk ' . $detail['calon_siswa']['nama']
                    . ' telah kami konfirmasi. Terima kasih.'
            );
        }

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
