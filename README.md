# Aplikasi PPDB

Aplikasi web Penerimaan Peserta Didik Baru (PPDB) — PHP native (OOP, tanpa framework), Bootstrap, MySQL.

## Fitur

- Halaman beranda: profil singkat sekolah, tata cara & persyaratan pendaftaran, tombol aksi, dan kontak/WhatsApp
- Pendaftaran online calon siswa (data orang tua, calon siswa, sekolah asal)
- Cek status pendaftaran (lewat nomor pendaftaran + NIK orang tua)
- Upload dokumen persyaratan (Kartu Keluarga, Akta Kelahiran, Ijazah/SKL, Pas Foto)
- Panel admin: verifikasi dokumen, jadwalkan wawancara, tetapkan hasil seleksi, konfirmasi pembayaran
- Kelola akun admin (tambah/edit/hapus, role `panitia`/`superadmin`) — khusus superadmin

## Kebutuhan

- [XAMPP](https://www.apachefriends.org) (Apache + MySQL + PHP 8+)
- Browser

## Cara Menjalankan

### 1. Letakkan proyek di folder XAMPP

Salin seluruh folder proyek ini ke:

```
C:\xampp\htdocs\ppdb-app
```

### 2. Buat database

1. Buka **XAMPP Control Panel**, jalankan **Apache** dan **MySQL**.
2. Buka phpMyAdmin di http://localhost/phpmyadmin, buat database baru bernama `ppdb_app`.
3. Import `docs/schema.sql` ke database tersebut (tab *Import* → pilih file → *Go*).
   File ini sudah berisi seed data: 4 gelombang pendaftaran contoh dan 1 akun admin demo.

### 3. Cek konfigurasi

`config/database.php` sudah cocok untuk setup XAMPP standar (`root`, tanpa password). Kalau MySQL Anda memakai kredensial lain, sesuaikan di file itu.

### 4. Buka di browser

| Halaman | URL |
|---|---|
| Beranda | http://localhost/ppdb-app/public/ |
| Form pendaftaran | http://localhost/ppdb-app/public/pendaftaran |
| Cek status pendaftaran | http://localhost/ppdb-app/public/status |
| Panel admin | http://localhost/ppdb-app/public/admin |

Beranda memakai foto placeholder (gradien navy) di bagian hero. Untuk pakai foto sekolah
sungguhan, taruh file di `public/assets/img/hero-sekolah.jpg` — otomatis terpakai tanpa
ubah kode. Nomor WhatsApp di blok kontak diatur langsung di `src/Views/home/index.php`
(variabel `$nomorWa`).

### Login admin (demo)

- Email: `admin@ppdb.local`
- Password: `admin123`

**Ganti password ini sebelum dipakai sungguhan** (buat hash baru dengan `password_hash()` di PHP, lalu `UPDATE admin_user`).

## Alur Kerja

1. Calon siswa mendaftar lewat form → mendapat nomor pendaftaran (`PPDB-2026-XXXXXX`).
2. Orang tua membuka halaman cek status (nomor + NIK) dan mengunggah dokumen yang diminta.
3. Admin memverifikasi tiap dokumen (terima/tolak, dengan catatan).
4. Setelah semua dokumen terverifikasi, admin menjadwalkan wawancara.
5. Admin menetapkan hasil seleksi — kalau **Diterima**, tagihan pembayaran otomatis dibuat sebesar biaya gelombang yang bersangkutan.
6. Admin mengonfirmasi pembayaran lunas.
7. Orang tua bisa memantau semua tahap ini kapan saja lewat halaman cek status — status dihitung otomatis dari data di atas, bukan diinput manual.

## Struktur Folder

```
config/       Koneksi database & pengaturan aplikasi
public/       Document root (arahkan Apache ke sini) — front controller, aset
  assets/css/app.css   Gaya kustom (palet navy/gold, hero, tombol WA, dll.)
  assets/img/          Taruh hero-sekolah.jpg di sini untuk foto beranda
src/
  Core/       Autoloader, Router, koneksi DB, autentikasi admin (session), helper view
  Models/     Akses data (Sekolah, Gelombang, Pendaftar, Dokumen, Wawancara, AdminUser)
  Controllers/ Logika tiap halaman (Home, Pendaftaran, Status, Auth, Admin)
  Views/      Template PHP + Bootstrap, dikelompokkan per controller
storage/uploads/  Dokumen yang diunggah (di luar public/, tidak bisa diakses langsung)
docs/         data-model.md (rancangan data), schema.sql (skema + seed),
              prototype.html (mockup UI standalone/offline, hanya referensi desain — bukan bagian aplikasi)
```

## Rute yang Tersedia

| Method | Path | Keterangan |
|---|---|---|
| GET | `/` | Beranda |
| GET/POST | `/pendaftaran` | Form pendaftaran calon siswa |
| GET | `/pendaftaran/berhasil` | Halaman sukses setelah mendaftar |
| GET/POST | `/status` | Cek status pendaftaran (nomor + NIK) |
| POST | `/status/dokumen/{jenis}` | Upload dokumen oleh orang tua |
| GET/POST | `/admin/login` · POST `/admin/logout` | Login/logout panitia |
| GET | `/admin` | Dashboard admin (daftar & filter pendaftar) |
| GET | `/admin/akun` | Kelola akun admin (khusus role `superadmin`) |
| GET | `/admin/akun/baru` | Form tambah akun admin |
| POST | `/admin/akun` | Simpan akun admin baru |
| GET | `/admin/akun/{id}/edit` | Form edit akun admin |
| POST | `/admin/akun/{id}` | Simpan perubahan akun admin |
| POST | `/admin/akun/{id}/hapus` | Hapus akun admin |
| GET | `/admin/pendaftar/{id}` | Kelola satu pendaftar |
| POST | `/admin/pendaftar/{id}/dokumen/{jenis}` | Verifikasi/tolak dokumen |
| GET | `/admin/pendaftar/{id}/dokumen/{jenis}/lihat` | Lihat file dokumen yang diunggah |
| POST | `/admin/pendaftar/{id}/wawancara` | Jadwalkan wawancara |
| POST | `/admin/pendaftar/{id}/hasil` | Tetapkan hasil seleksi |
| POST | `/admin/pendaftar/{id}/pembayaran/lunas` | Konfirmasi pembayaran lunas |

## Keterbatasan Saat Ini

- Satu sekolah per deployment (bukan platform multi-sekolah).
- Belum ada notifikasi email/WhatsApp otomatis ke orang tua saat status berubah — tombol WA di
  beranda hanya membuka chat manual, bukan integrasi otomatis.
- Foto hero di beranda masih placeholder sampai `public/assets/img/hero-sekolah.jpg` diisi.

## Menjalankan dengan PHP Built-in Server (opsional, untuk pengembangan cepat)

Selain lewat XAMPP/Apache, proyek ini juga bisa dijalankan tanpa Apache sama sekali:

```
php -S 127.0.0.1:8000 -t public public/index.php
```

`public/index.php` sudah menangani penyajian file statis (CSS/JS/gambar) secara benar saat
dijalankan lewat mode ini, jadi tidak perlu konfigurasi tambahan. Cocok untuk uji cepat tanpa
menyalin proyek ke `htdocs`. MySQL tetap harus jalan (lewat XAMPP Control Panel seperti biasa).
