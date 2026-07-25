# Aplikasi PPDB

Aplikasi web Penerimaan Peserta Didik Baru (PPDB) — PHP native (OOP, tanpa framework), Bootstrap, MySQL.

## Fitur

- Pendaftaran online calon siswa (data orang tua, calon siswa, sekolah asal)
- Cek status pendaftaran (lewat nomor pendaftaran + NIK orang tua)
- Upload dokumen persyaratan (Kartu Keluarga, Akta Kelahiran, Ijazah/SKL, Pas Foto)
- Panel admin: verifikasi dokumen, jadwalkan wawancara, tetapkan hasil seleksi, konfirmasi pembayaran

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
| Form pendaftaran | http://localhost/ppdb-app/public/pendaftaran |
| Cek status pendaftaran | http://localhost/ppdb-app/public/status |
| Panel admin | http://localhost/ppdb-app/public/admin |

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
src/
  Core/       Autoloader, Router, koneksi DB, autentikasi admin, helper view
  Models/     Akses data (Pendaftar, Dokumen, Gelombang, dst.)
  Controllers/ Logika tiap halaman
  Views/      Template PHP + Bootstrap
storage/uploads/  Dokumen yang diunggah (di luar public/, tidak bisa diakses langsung)
docs/         data-model.md (rancangan data) & schema.sql (skema + seed)
```

## Keterbatasan Saat Ini

- Satu sekolah per deployment (bukan platform multi-sekolah).
- Belum ada halaman kelola akun admin lewat UI (tambah/hapus admin masih manual lewat database).
- Belum ada notifikasi email/WhatsApp otomatis ke orang tua saat status berubah.
