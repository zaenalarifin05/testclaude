<?php

namespace App\Controllers;

use App\Models\Gelombang;
use App\Models\Pendaftar;

class PendaftaranController
{
    private const JENJANG_TUJUAN = ['SD', 'SMP', 'SMA'];
    private const JENJANG_ASAL = ['TK/PAUD', 'SD', 'SMP'];
    private const HUBUNGAN = ['ayah', 'ibu', 'wali'];

    public function form(): void
    {
        $this->render(Gelombang::aktif(), [], []);
    }

    public function simpan(): void
    {
        $gelombang = Gelombang::aktif();

        if ($gelombang === null) {
            http_response_code(422);
            $this->render(null, ['gelombang' => 'Tidak ada gelombang pendaftaran yang sedang aktif saat ini.'], $_POST);
            return;
        }

        [$data, $errors] = $this->validasi($_POST);

        if (!empty($errors)) {
            http_response_code(422);
            $this->render($gelombang, $errors, $_POST);
            return;
        }

        $data['gelombang_id'] = $gelombang['id'];
        $hasil = Pendaftar::buat($data);

        header('Location: /pendaftaran/berhasil?nomor=' . urlencode($hasil['nomor_pendaftaran']));
        exit;
    }

    public function berhasil(): void
    {
        $nomorPendaftaran = $_GET['nomor'] ?? '';
        $title = 'Pendaftaran Berhasil';

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/pendaftaran/berhasil.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }

    /**
     * @return array{0: array, 1: array<string, string>}
     */
    private function validasi(array $input): array
    {
        $errors = [];
        $alamatSama = isset($input['alamat_sama_dengan_ortu']);

        $data = [
            'orang_tua' => [
                'nama' => trim($input['p_nama'] ?? ''),
                'nik' => trim($input['p_nik'] ?? ''),
                'hubungan' => $input['p_hubungan'] ?? '',
                'no_hp' => trim($input['p_hp'] ?? ''),
                'email' => trim($input['p_email'] ?? ''),
                'alamat' => trim($input['p_alamat'] ?? ''),
            ],
            'calon_siswa' => [
                'nama' => trim($input['s_nama'] ?? ''),
                'jenjang_tujuan' => $input['s_jenjang'] ?? '',
                'tempat_lahir' => trim($input['s_tempat'] ?? ''),
                'tanggal_lahir' => $input['s_tgl'] ?? '',
                'jenis_kelamin' => $input['s_jk'] ?? '',
                'nisn' => trim($input['s_nisn'] ?? ''),
                'alamat_sama_dengan_ortu' => $alamatSama,
                'alamat' => trim($input['s_alamat'] ?? ''),
            ],
            'sekolah_asal' => [
                'nama_sekolah' => trim($input['sc_nama'] ?? ''),
                'jenjang' => $input['sc_jenjang'] ?? '',
                'tahun_lulus' => trim($input['sc_tahun'] ?? ''),
            ],
        ];

        if ($data['orang_tua']['nama'] === '') {
            $errors['p_nama'] = 'Nama orang tua/wali wajib diisi.';
        }
        if (!preg_match('/^\d{16}$/', $data['orang_tua']['nik'])) {
            $errors['p_nik'] = 'NIK harus berupa 16 digit angka.';
        }
        if (!in_array($data['orang_tua']['hubungan'], self::HUBUNGAN, true)) {
            $errors['p_hubungan'] = 'Pilih hubungan dengan calon siswa.';
        }
        if ($data['orang_tua']['no_hp'] === '') {
            $errors['p_hp'] = 'No. HP/WhatsApp wajib diisi.';
        }
        if (!filter_var($data['orang_tua']['email'], FILTER_VALIDATE_EMAIL)) {
            $errors['p_email'] = 'Email tidak valid.';
        }
        if ($data['orang_tua']['alamat'] === '') {
            $errors['p_alamat'] = 'Alamat wajib diisi.';
        }

        if ($data['calon_siswa']['nama'] === '') {
            $errors['s_nama'] = 'Nama calon siswa wajib diisi.';
        }
        if (!in_array($data['calon_siswa']['jenjang_tujuan'], self::JENJANG_TUJUAN, true)) {
            $errors['s_jenjang'] = 'Pilih jenjang yang dituju.';
        }
        if ($data['calon_siswa']['tempat_lahir'] === '') {
            $errors['s_tempat'] = 'Tempat lahir wajib diisi.';
        }
        if ($data['calon_siswa']['tanggal_lahir'] === '') {
            $errors['s_tgl'] = 'Tanggal lahir wajib diisi.';
        }
        if (!in_array($data['calon_siswa']['jenis_kelamin'], ['L', 'P'], true)) {
            $errors['s_jk'] = 'Pilih jenis kelamin.';
        }
        if (!$alamatSama && $data['calon_siswa']['alamat'] === '') {
            $errors['s_alamat'] = 'Alamat calon siswa wajib diisi jika berbeda dari alamat orang tua.';
        }

        if ($data['sekolah_asal']['nama_sekolah'] === '') {
            $errors['sc_nama'] = 'Nama sekolah asal wajib diisi.';
        }
        if (!in_array($data['sekolah_asal']['jenjang'], self::JENJANG_ASAL, true)) {
            $errors['sc_jenjang'] = 'Pilih jenjang sekolah asal.';
        }
        if ($data['sekolah_asal']['tahun_lulus'] === '') {
            $errors['sc_tahun'] = 'Tahun lulus wajib diisi.';
        }

        return [$data, $errors];
    }

    private function render(?array $gelombang, array $errors, array $old): void
    {
        $title = 'Formulir Pendaftaran PPDB';
        $jenjangTujuan = self::JENJANG_TUJUAN;
        $jenjangAsal = self::JENJANG_ASAL;

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/pendaftaran/form.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }
}
