<?php

namespace App\Models;

use PDO;
use Throwable;

class Pendaftar extends Model
{
    /**
     * Membuat satu pendaftaran lengkap (pendaftar + orang tua + calon siswa +
     * sekolah asal + baris dokumen kosong) dalam satu transaksi.
     *
     * @param array{
     *   gelombang_id: int,
     *   orang_tua: array{nama: string, nik: string, hubungan: string, no_hp: string, email: string, alamat: string},
     *   calon_siswa: array{nama: string, jenjang_tujuan: string, tempat_lahir: string, tanggal_lahir: string, jenis_kelamin: string, nisn: ?string, alamat_sama_dengan_ortu: bool, alamat: ?string},
     *   sekolah_asal: array{nama_sekolah: string, jenjang: string, tahun_lulus: string}
     * } $data
     * @return array{id: int, nomor_pendaftaran: string}
     */
    public static function buat(array $data): array
    {
        $db = self::db();
        $db->beginTransaction();

        try {
            $placeholder = 'TEMP-' . bin2hex(random_bytes(6)); // muat di nomor_pendaftaran VARCHAR(20)

            $stmt = $db->prepare(
                'INSERT INTO pendaftar (nomor_pendaftaran, gelombang_id) VALUES (:nomor, :gelombang_id)'
            );
            $stmt->execute([
                'nomor' => $placeholder,
                'gelombang_id' => $data['gelombang_id'],
            ]);

            $id = (int) $db->lastInsertId();
            $nomorPendaftaran = sprintf('PPDB-%s-%06d', date('Y'), $id);

            $update = $db->prepare('UPDATE pendaftar SET nomor_pendaftaran = :nomor WHERE id = :id');
            $update->execute(['nomor' => $nomorPendaftaran, 'id' => $id]);

            self::simpanOrangTua($db, $id, $data['orang_tua']);
            self::simpanCalonSiswa($db, $id, $data['calon_siswa']);
            self::simpanSekolahAsal($db, $id, $data['sekolah_asal']);
            Dokumen::initUntukPendaftar($id, $db);

            $db->commit();

            return ['id' => $id, 'nomor_pendaftaran' => $nomorPendaftaran];
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    /**
     * Cari pendaftar lewat nomor pendaftaran + NIK orang tua (dipakai untuk
     * halaman cek status publik, jadi keduanya harus cocok sebagai kunci akses).
     */
    public static function cariByNomorDanNik(string $nomorPendaftaran, string $nik): ?array
    {
        $stmt = self::db()->prepare(
            'SELECT p.id FROM pendaftar p
             INNER JOIN orang_tua o ON o.pendaftar_id = p.id
             WHERE p.nomor_pendaftaran = :nomor AND o.nik = :nik'
        );
        $stmt->execute(['nomor' => $nomorPendaftaran, 'nik' => $nik]);
        $row = $stmt->fetch();

        return $row ? self::detail((int) $row['id']) : null;
    }

    /**
     * Ambil satu pendaftar beserta seluruh data terkait (orang tua, calon siswa,
     * sekolah asal, gelombang, dokumen, wawancara, hasil seleksi, pembayaran).
     */
    public static function detail(int $id): ?array
    {
        $db = self::db();

        $stmt = $db->prepare('SELECT * FROM pendaftar WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $pendaftar = $stmt->fetch();

        if ($pendaftar === false) {
            return null;
        }

        $satuBaris = function (string $table) use ($db, $id): ?array {
            $stmt = $db->prepare("SELECT * FROM {$table} WHERE pendaftar_id = :id");
            $stmt->execute(['id' => $id]);

            return $stmt->fetch() ?: null;
        };

        $gelombangStmt = $db->prepare('SELECT * FROM gelombang WHERE id = :id');
        $gelombangStmt->execute(['id' => $pendaftar['gelombang_id']]);

        $dokumenStmt = $db->prepare(
            'SELECT jenis, status, catatan_verifikasi, file_url FROM dokumen WHERE pendaftar_id = :id ORDER BY jenis'
        );
        $dokumenStmt->execute(['id' => $id]);

        return [
            'pendaftar' => $pendaftar,
            'gelombang' => $gelombangStmt->fetch() ?: null,
            'orang_tua' => $satuBaris('orang_tua'),
            'calon_siswa' => $satuBaris('calon_siswa'),
            'sekolah_asal' => $satuBaris('sekolah_asal'),
            'dokumen' => $dokumenStmt->fetchAll(),
            'wawancara' => $satuBaris('wawancara'),
            'hasil_seleksi' => $satuBaris('hasil_seleksi'),
            'pembayaran' => $satuBaris('pembayaran'),
        ];
    }

    /**
     * Hitung tahapan pendaftaran dari kondisi data terkait — status TIDAK
     * disimpan sebagai kolom (lihat docs/data-model.md), supaya tidak pernah
     * nyasar dari data aslinya.
     */
    public static function hitungTahap(array $detail): string
    {
        $dokumen = $detail['dokumen'];
        $semuaTerverifikasi = count($dokumen) > 0;
        $semuaTerunggah = count($dokumen) > 0;

        foreach ($dokumen as $d) {
            if ($d['status'] !== 'terverifikasi') {
                $semuaTerverifikasi = false;
            }
            if ($d['status'] === 'belum_upload') {
                $semuaTerunggah = false;
            }
        }

        return self::tentukanTahap(
            $semuaTerverifikasi,
            $semuaTerunggah,
            $detail['wawancara'],
            $detail['hasil_seleksi'],
            $detail['pembayaran']
        );
    }

    /**
     * Logika inti perhitungan tahap, dipisah dari hitungTahap() supaya bisa
     * dipakai juga oleh ringkasanUntukAdmin() yang mengambil status dokumen
     * lewat agregat SQL (bukan array baris dokumen penuh).
     */
    private static function tentukanTahap(
        bool $semuaTerverifikasi,
        bool $semuaTerunggah,
        ?array $wawancara,
        ?array $hasil,
        ?array $pembayaran
    ): string {
        if ($hasil !== null) {
            if ($hasil['hasil'] === 'tidak_diterima') {
                return 'tidak_diterima';
            }
            if ($hasil['hasil'] === 'cadangan') {
                return 'cadangan';
            }

            if ($pembayaran === null || $pembayaran['status'] === 'menunggu_pembayaran') {
                return 'menunggu_pembayaran';
            }
            if ($pembayaran['status'] === 'menunggu_konfirmasi') {
                return 'menunggu_konfirmasi_bayar';
            }

            return 'lunas';
        }

        if ($wawancara !== null) {
            return strtotime($wawancara['tanggal']) >= strtotime('today') ? 'menunggu_wawancara' : 'menunggu_hasil';
        }

        if ($semuaTerverifikasi) {
            return 'menunggu_jadwal';
        }
        if ($semuaTerunggah) {
            return 'menunggu_verifikasi';
        }

        return 'lengkapi_dokumen';
    }

    /**
     * Daftar ringkas semua pendaftar untuk dashboard admin, dengan tahap
     * sudah dihitung. Pakai agregat SQL (bukan detail() per baris) supaya
     * tidak N+1 query untuk daftar yang bisa berisi banyak pendaftar.
     */
    public static function ringkasanUntukAdmin(): array
    {
        $rows = self::db()->query(
            "SELECT p.id, p.nomor_pendaftaran, p.created_at,
                    cs.nama AS nama_siswa, cs.jenjang_tujuan,
                    g.kode AS gelombang_kode, g.label AS gelombang_label,
                    COALESCE(dok.total, 0) AS dokumen_total,
                    COALESCE(dok.terverifikasi, 0) AS dokumen_terverifikasi,
                    COALESCE(dok.terunggah, 0) AS dokumen_terunggah,
                    w.tanggal AS wawancara_tanggal,
                    hs.hasil AS hasil,
                    pb.status AS status_pembayaran
             FROM pendaftar p
             INNER JOIN calon_siswa cs ON cs.pendaftar_id = p.id
             INNER JOIN gelombang g ON g.id = p.gelombang_id
             LEFT JOIN (
                 SELECT pendaftar_id,
                        COUNT(*) AS total,
                        SUM(status = 'terverifikasi') AS terverifikasi,
                        SUM(status != 'belum_upload') AS terunggah
                 FROM dokumen
                 GROUP BY pendaftar_id
             ) dok ON dok.pendaftar_id = p.id
             LEFT JOIN wawancara w ON w.pendaftar_id = p.id
             LEFT JOIN hasil_seleksi hs ON hs.pendaftar_id = p.id
             LEFT JOIN pembayaran pb ON pb.pendaftar_id = p.id
             ORDER BY p.created_at DESC"
        )->fetchAll();

        foreach ($rows as &$row) {
            $row['tahap'] = self::tentukanTahap(
                $row['dokumen_total'] > 0 && $row['dokumen_terverifikasi'] == $row['dokumen_total'],
                $row['dokumen_total'] > 0 && $row['dokumen_terunggah'] == $row['dokumen_total'],
                $row['wawancara_tanggal'] !== null ? ['tanggal' => $row['wawancara_tanggal']] : null,
                $row['hasil'] !== null ? ['hasil' => $row['hasil']] : null,
                $row['status_pembayaran'] !== null ? ['status' => $row['status_pembayaran']] : null
            );
        }
        unset($row);

        return $rows;
    }

    /**
     * Tetapkan hasil seleksi. Kalau diterima, otomatis buat tagihan pembayaran
     * sebesar biaya_ppdb gelombang yang bersangkutan (kalau belum ada).
     */
    public static function tetapkanHasil(int $pendaftarId, string $hasil, int $adminId): void
    {
        $db = self::db();
        $db->beginTransaction();

        try {
            $stmt = $db->prepare(
                'INSERT INTO hasil_seleksi (pendaftar_id, hasil, ditetapkan_oleh)
                 VALUES (:pendaftar_id, :hasil, :admin_id)
                 ON DUPLICATE KEY UPDATE hasil = VALUES(hasil), ditetapkan_oleh = VALUES(ditetapkan_oleh), ditetapkan_at = NOW()'
            );
            $stmt->execute(['pendaftar_id' => $pendaftarId, 'hasil' => $hasil, 'admin_id' => $adminId]);

            if ($hasil === 'diterima') {
                $biaya = $db->prepare(
                    'SELECT g.biaya_ppdb FROM gelombang g
                     INNER JOIN pendaftar p ON p.gelombang_id = g.id
                     WHERE p.id = :pendaftar_id'
                );
                $biaya->execute(['pendaftar_id' => $pendaftarId]);
                $jumlahTagihan = (float) $biaya->fetchColumn();

                $pembayaran = $db->prepare(
                    'INSERT IGNORE INTO pembayaran (pendaftar_id, jumlah_tagihan, status)
                     VALUES (:pendaftar_id, :jumlah, "menunggu_pembayaran")'
                );
                $pembayaran->execute(['pendaftar_id' => $pendaftarId, 'jumlah' => $jumlahTagihan]);
            }

            $db->commit();
        } catch (Throwable $e) {
            $db->rollBack();
            throw $e;
        }
    }

    public static function konfirmasiLunas(int $pendaftarId, int $adminId): bool
    {
        $stmt = self::db()->prepare(
            'UPDATE pembayaran
             SET status = "lunas", dikonfirmasi_at = NOW(), dikonfirmasi_oleh = :admin_id
             WHERE pendaftar_id = :pendaftar_id'
        );

        return $stmt->execute(['pendaftar_id' => $pendaftarId, 'admin_id' => $adminId]);
    }

    private static function simpanOrangTua(PDO $db, int $pendaftarId, array $d): void
    {
        $stmt = $db->prepare(
            'INSERT INTO orang_tua (pendaftar_id, nama, nik, hubungan, no_hp, email, alamat)
             VALUES (:pendaftar_id, :nama, :nik, :hubungan, :no_hp, :email, :alamat)'
        );
        $stmt->execute([
            'pendaftar_id' => $pendaftarId,
            'nama' => $d['nama'],
            'nik' => $d['nik'],
            'hubungan' => $d['hubungan'],
            'no_hp' => $d['no_hp'],
            'email' => $d['email'],
            'alamat' => $d['alamat'],
        ]);
    }

    private static function simpanCalonSiswa(PDO $db, int $pendaftarId, array $d): void
    {
        $stmt = $db->prepare(
            'INSERT INTO calon_siswa
                (pendaftar_id, nama, jenjang_tujuan, tempat_lahir, tanggal_lahir, jenis_kelamin, nisn, alamat_sama_dengan_ortu, alamat)
             VALUES
                (:pendaftar_id, :nama, :jenjang_tujuan, :tempat_lahir, :tanggal_lahir, :jenis_kelamin, :nisn, :alamat_sama, :alamat)'
        );
        $stmt->execute([
            'pendaftar_id' => $pendaftarId,
            'nama' => $d['nama'],
            'jenjang_tujuan' => $d['jenjang_tujuan'],
            'tempat_lahir' => $d['tempat_lahir'],
            'tanggal_lahir' => $d['tanggal_lahir'],
            'jenis_kelamin' => $d['jenis_kelamin'],
            'nisn' => $d['nisn'] !== '' ? $d['nisn'] : null,
            'alamat_sama' => $d['alamat_sama_dengan_ortu'] ? 1 : 0,
            'alamat' => $d['alamat_sama_dengan_ortu'] ? null : $d['alamat'],
        ]);
    }

    private static function simpanSekolahAsal(PDO $db, int $pendaftarId, array $d): void
    {
        $stmt = $db->prepare(
            'INSERT INTO sekolah_asal (pendaftar_id, nama_sekolah, jenjang, tahun_lulus)
             VALUES (:pendaftar_id, :nama_sekolah, :jenjang, :tahun_lulus)'
        );
        $stmt->execute([
            'pendaftar_id' => $pendaftarId,
            'nama_sekolah' => $d['nama_sekolah'],
            'jenjang' => $d['jenjang'],
            'tahun_lulus' => $d['tahun_lulus'],
        ]);
    }
}
