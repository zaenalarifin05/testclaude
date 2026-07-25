-- ============================================================
-- PPDB App — Skema MySQL
-- Diturunkan dari docs/data-model.md. Satu sekolah per deployment
-- (lihat data-model.md), jadi tabel `sekolah` selalu berisi 1 baris.
-- ============================================================

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ------------------------------------------------------------
-- 1. Sekolah (pengaturan tunggal, selalu 1 baris)
-- ------------------------------------------------------------
CREATE TABLE sekolah (
    id            TINYINT UNSIGNED NOT NULL PRIMARY KEY DEFAULT 1,
    npsn          VARCHAR(20)  NOT NULL,
    nama          VARCHAR(150) NOT NULL,
    jenjang       ENUM('SD', 'SMP', 'SMA', 'SMK') NOT NULL,
    alamat        TEXT NULL,
    tahun_ajaran  VARCHAR(9)   NOT NULL COMMENT 'contoh: 2026/2027',
    updated_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT chk_sekolah_single_row CHECK (id = 1)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 2. Gelombang
-- ------------------------------------------------------------
CREATE TABLE gelombang (
    id                INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    kode              VARCHAR(30)  NOT NULL UNIQUE COMMENT 'slug: early_bird, tahap1, tahap2, tahap_akhir',
    label             VARCHAR(100) NOT NULL,
    tanggal_mulai     DATE NOT NULL,
    tanggal_selesai   DATE NOT NULL,
    kuota             INT UNSIGNED NOT NULL DEFAULT 0,
    biaya_ppdb        DECIMAL(12, 2) NOT NULL DEFAULT 0,
    urutan            INT UNSIGNED NOT NULL DEFAULT 0,
    aktif             TINYINT(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 3. Admin User
-- ------------------------------------------------------------
CREATE TABLE admin_user (
    id             INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nama           VARCHAR(100) NOT NULL,
    email          VARCHAR(150) NOT NULL UNIQUE,
    password_hash  VARCHAR(255) NOT NULL,
    role           ENUM('panitia', 'superadmin') NOT NULL DEFAULT 'panitia',
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 4. Pendaftar (entitas pusat)
-- ------------------------------------------------------------
CREATE TABLE pendaftar (
    id                  INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    nomor_pendaftaran   VARCHAR(20) NOT NULL UNIQUE COMMENT 'format: PPDB-2026-000123',
    gelombang_id        INT UNSIGNED NOT NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pendaftar_gelombang FOREIGN KEY (gelombang_id)
        REFERENCES gelombang (id) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_pendaftar_gelombang ON pendaftar (gelombang_id);

-- ------------------------------------------------------------
-- 5. Orang Tua / Wali (1:1 dengan pendaftar)
-- ------------------------------------------------------------
CREATE TABLE orang_tua (
    pendaftar_id  INT UNSIGNED NOT NULL PRIMARY KEY,
    nama          VARCHAR(150) NOT NULL,
    nik           CHAR(16) NOT NULL,
    hubungan      ENUM('ayah', 'ibu', 'wali') NOT NULL,
    no_hp         VARCHAR(20) NOT NULL,
    email         VARCHAR(150) NOT NULL,
    alamat        TEXT NOT NULL,
    CONSTRAINT fk_orangtua_pendaftar FOREIGN KEY (pendaftar_id)
        REFERENCES pendaftar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT chk_orangtua_nik CHECK (nik REGEXP '^[0-9]{16}$')
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 6. Calon Siswa (1:1 dengan pendaftar)
-- ------------------------------------------------------------
CREATE TABLE calon_siswa (
    pendaftar_id              INT UNSIGNED NOT NULL PRIMARY KEY,
    nama                      VARCHAR(150) NOT NULL,
    jenjang_tujuan            ENUM('SD', 'SMP', 'SMA') NOT NULL,
    tempat_lahir              VARCHAR(100) NOT NULL,
    tanggal_lahir             DATE NOT NULL,
    jenis_kelamin             ENUM('L', 'P') NOT NULL,
    nisn                      VARCHAR(15) NULL,
    alamat_sama_dengan_ortu   TINYINT(1) NOT NULL DEFAULT 1,
    alamat                    TEXT NULL COMMENT 'diisi hanya jika alamat_sama_dengan_ortu = 0',
    CONSTRAINT fk_calonsiswa_pendaftar FOREIGN KEY (pendaftar_id)
        REFERENCES pendaftar (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 7. Sekolah Asal (1:1 dengan pendaftar, teks bebas)
-- ------------------------------------------------------------
CREATE TABLE sekolah_asal (
    pendaftar_id  INT UNSIGNED NOT NULL PRIMARY KEY,
    nama_sekolah  VARCHAR(150) NOT NULL,
    jenjang       ENUM('TK/PAUD', 'SD', 'SMP') NOT NULL,
    tahun_lulus   VARCHAR(4) NOT NULL,
    CONSTRAINT fk_sekolahasal_pendaftar FOREIGN KEY (pendaftar_id)
        REFERENCES pendaftar (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 8. Dokumen (1:N dengan pendaftar — 1 baris per jenis dokumen)
-- ------------------------------------------------------------
CREATE TABLE dokumen (
    id                   INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    pendaftar_id         INT UNSIGNED NOT NULL,
    jenis                ENUM('kk', 'akta', 'ijazah', 'foto') NOT NULL,
    file_url             VARCHAR(255) NULL COMMENT 'path relatif di folder uploads/',
    status               ENUM('belum_upload', 'menunggu_verifikasi', 'terverifikasi', 'ditolak')
                             NOT NULL DEFAULT 'belum_upload',
    catatan_verifikasi   TEXT NULL,
    uploaded_at          TIMESTAMP NULL,
    verified_at          TIMESTAMP NULL,
    CONSTRAINT fk_dokumen_pendaftar FOREIGN KEY (pendaftar_id)
        REFERENCES pendaftar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT uq_dokumen_pendaftar_jenis UNIQUE (pendaftar_id, jenis)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 9. Wawancara (0/1:1 dengan pendaftar)
-- ------------------------------------------------------------
CREATE TABLE wawancara (
    pendaftar_id  INT UNSIGNED NOT NULL PRIMARY KEY,
    tanggal       DATE NOT NULL,
    jam           TIME NOT NULL,
    lokasi        VARCHAR(150) NOT NULL,
    CONSTRAINT fk_wawancara_pendaftar FOREIGN KEY (pendaftar_id)
        REFERENCES pendaftar (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 10. Hasil Seleksi (0/1:1 dengan pendaftar)
-- ------------------------------------------------------------
CREATE TABLE hasil_seleksi (
    pendaftar_id     INT UNSIGNED NOT NULL PRIMARY KEY,
    hasil            ENUM('diterima', 'tidak_diterima', 'cadangan') NOT NULL,
    ditetapkan_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    ditetapkan_oleh  INT UNSIGNED NULL,
    CONSTRAINT fk_hasil_pendaftar FOREIGN KEY (pendaftar_id)
        REFERENCES pendaftar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_hasil_admin FOREIGN KEY (ditetapkan_oleh)
        REFERENCES admin_user (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 11. Pembayaran (0/1:1 dengan pendaftar)
-- ------------------------------------------------------------
CREATE TABLE pembayaran (
    pendaftar_id         INT UNSIGNED NOT NULL PRIMARY KEY,
    jumlah_tagihan       DECIMAL(12, 2) NOT NULL,
    status               ENUM('menunggu_pembayaran', 'menunggu_konfirmasi', 'lunas')
                             NOT NULL DEFAULT 'menunggu_pembayaran',
    bukti_transfer_url   VARCHAR(255) NULL,
    dikonfirmasi_at      TIMESTAMP NULL,
    dikonfirmasi_oleh    INT UNSIGNED NULL,
    CONSTRAINT fk_pembayaran_pendaftar FOREIGN KEY (pendaftar_id)
        REFERENCES pendaftar (id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_pembayaran_admin FOREIGN KEY (dikonfirmasi_oleh)
        REFERENCES admin_user (id) ON DELETE SET NULL ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- ------------------------------------------------------------
-- 12. Notifikasi (1:N dengan pendaftar)
-- ------------------------------------------------------------
CREATE TABLE notifikasi (
    id            INT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    pendaftar_id  INT UNSIGNED NOT NULL,
    judul         VARCHAR(150) NOT NULL,
    pesan         TEXT NOT NULL,
    channel       ENUM('wa', 'email') NOT NULL,
    dikirim_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notifikasi_pendaftar FOREIGN KEY (pendaftar_id)
        REFERENCES pendaftar (id) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE INDEX idx_notifikasi_pendaftar ON notifikasi (pendaftar_id);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- Seed data secukupnya (sesuaikan sebelum dipakai di production)
-- ============================================================

INSERT INTO sekolah (id, npsn, nama, jenjang, alamat, tahun_ajaran) VALUES
    (1, '00000000', 'Nama Sekolah', 'SMA', 'Alamat sekolah', '2026/2027');

INSERT INTO gelombang (kode, label, tanggal_mulai, tanggal_selesai, kuota, biaya_ppdb, urutan, aktif) VALUES
    ('early_bird', 'Early Bird', '2026-01-01', '2026-02-28', 60, 500000, 1, 0),
    ('tahap1',     'Tahap 1',    '2026-03-01', '2026-04-30', 60, 750000, 2, 1),
    ('tahap2',     'Tahap 2',    '2026-05-01', '2026-06-30', 40, 1000000, 3, 0),
    ('tahap_akhir','Tahap Akhir','2026-07-01', '2026-07-31', 20, 1250000, 4, 0);

-- Akun admin demo — email: admin@ppdb.local, password: admin123
-- GANTI password ini sebelum dipakai di production (buat hash baru dengan
-- password_hash() dan UPDATE admin_user, jangan simpan password polos di sini).
INSERT INTO admin_user (nama, email, password_hash, role) VALUES
    ('Admin PPDB', 'admin@ppdb.local', '$2y$12$2FOzC1SQfGUz4ZMpAybv4OHEdNlUcMFSGqjjjZ/Y3W9m3DoOoo/VG', 'superadmin');
