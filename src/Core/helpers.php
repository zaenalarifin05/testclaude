<?php

function e(?string $value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function tahap_label(string $tahap): string
{
    static $labels = [
        'lengkapi_dokumen' => 'Lengkapi Dokumen',
        'menunggu_verifikasi' => 'Menunggu Verifikasi Dokumen',
        'menunggu_jadwal' => 'Menunggu Jadwal Wawancara',
        'menunggu_wawancara' => 'Menunggu Wawancara',
        'menunggu_hasil' => 'Menunggu Hasil Seleksi',
        'tidak_diterima' => 'Tidak Diterima',
        'cadangan' => 'Cadangan',
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_konfirmasi_bayar' => 'Menunggu Konfirmasi Pembayaran',
        'lunas' => 'Lunas',
    ];

    return $labels[$tahap] ?? $tahap;
}

function tahap_warna(string $tahap): string
{
    static $warna = [
        'lengkapi_dokumen' => 'secondary',
        'menunggu_verifikasi' => 'warning',
        'menunggu_jadwal' => 'warning',
        'menunggu_wawancara' => 'warning',
        'menunggu_hasil' => 'warning',
        'tidak_diterima' => 'danger',
        'cadangan' => 'info',
        'menunggu_pembayaran' => 'warning',
        'menunggu_konfirmasi_bayar' => 'warning',
        'lunas' => 'success',
    ];

    return $warna[$tahap] ?? 'secondary';
}

/** @return list<string> */
function semua_tahap(): array
{
    return [
        'lengkapi_dokumen',
        'menunggu_verifikasi',
        'menunggu_jadwal',
        'menunggu_wawancara',
        'menunggu_hasil',
        'menunggu_pembayaran',
        'menunggu_konfirmasi_bayar',
        'lunas',
        'tidak_diterima',
        'cadangan',
    ];
}

function hasil_label(string $hasil): string
{
    static $labels = [
        'diterima' => 'Diterima',
        'tidak_diterima' => 'Tidak Diterima',
        'cadangan' => 'Cadangan',
    ];

    return $labels[$hasil] ?? $hasil;
}

function pembayaran_label(string $status): string
{
    static $labels = [
        'menunggu_pembayaran' => 'Menunggu Pembayaran',
        'menunggu_konfirmasi' => 'Menunggu Konfirmasi',
        'lunas' => 'Lunas',
    ];

    return $labels[$status] ?? $status;
}

function dokumen_label(string $jenis): string
{
    static $labels = [
        'kk' => 'Kartu Keluarga (KK)',
        'akta' => 'Akta Kelahiran',
        'ijazah' => 'Ijazah / SKL',
        'foto' => 'Pas Foto 3x4',
    ];

    return $labels[$jenis] ?? $jenis;
}

function status_dokumen_label(string $status): string
{
    static $labels = [
        'belum_upload' => 'Belum Diunggah',
        'menunggu_verifikasi' => 'Menunggu Verifikasi',
        'terverifikasi' => 'Terverifikasi',
        'ditolak' => 'Ditolak',
    ];

    return $labels[$status] ?? $status;
}

function status_dokumen_warna(string $status): string
{
    static $warna = [
        'belum_upload' => 'secondary',
        'menunggu_verifikasi' => 'warning',
        'terverifikasi' => 'success',
        'ditolak' => 'danger',
    ];

    return $warna[$status] ?? 'secondary';
}
