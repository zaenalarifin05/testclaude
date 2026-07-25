<?php
/** @var array $detail */
/** @var string $tahap */

$id = $detail['pendaftar']['id'];
$semuaTerverifikasi = true;
foreach ($detail['dokumen'] as $d) {
    if ($d['status'] !== 'terverifikasi') {
        $semuaTerverifikasi = false;
        break;
    }
}
$bolehJadwalkanWawancara = $detail['wawancara'] !== null || $semuaTerverifikasi;
?>

<a href="/admin" class="d-inline-block mb-3">&larr; Kembali ke daftar</a>

<div class="d-flex justify-content-between align-items-start mb-4">
    <div>
        <h1 class="h3 mb-1"><?= e($detail['calon_siswa']['nama']) ?></h1>
        <p class="text-muted mb-0"><?= e($detail['pendaftar']['nomor_pendaftaran']) ?> — <?= e($detail['gelombang']['label'] ?? '-') ?></p>
    </div>
    <span class="badge bg-<?= e(tahap_warna($tahap)) ?> fs-6"><?= e(tahap_label($tahap)) ?></span>
</div>

<div class="row">
    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 card-title mb-3">Data Orang Tua / Wali</h2>
                <p class="mb-1"><?= e($detail['orang_tua']['nama']) ?> (<?= e($detail['orang_tua']['hubungan']) ?>)</p>
                <p class="mb-1">NIK: <?= e($detail['orang_tua']['nik']) ?></p>
                <p class="mb-1"><?= e($detail['orang_tua']['no_hp']) ?> — <?= e($detail['orang_tua']['email']) ?></p>
                <p class="mb-0"><?= e($detail['orang_tua']['alamat']) ?></p>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 card-title mb-3">Calon Siswa &amp; Sekolah Asal</h2>
                <p class="mb-1">Jenjang tujuan: <?= e($detail['calon_siswa']['jenjang_tujuan']) ?></p>
                <p class="mb-1">TTL: <?= e($detail['calon_siswa']['tempat_lahir']) ?>, <?= e($detail['calon_siswa']['tanggal_lahir']) ?></p>
                <p class="mb-1">Jenis kelamin: <?= $detail['calon_siswa']['jenis_kelamin'] === 'L' ? 'Laki-laki' : 'Perempuan' ?></p>
                <p class="mb-0">Sekolah asal: <?= e($detail['sekolah_asal']['nama_sekolah']) ?> (<?= e($detail['sekolah_asal']['jenjang']) ?>, lulus <?= e($detail['sekolah_asal']['tahun_lulus']) ?>)</p>
            </div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 card-title mb-3">Verifikasi Dokumen</h2>
                <?php foreach ($detail['dokumen'] as $dok): ?>
                    <div class="border-bottom pb-2 mb-2">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong><?= e(dokumen_label($dok['jenis'])) ?></strong>
                            <span class="badge bg-<?= e(status_dokumen_warna($dok['status'])) ?>"><?= e(status_dokumen_label($dok['status'])) ?></span>
                        </div>
                        <?php if ($dok['catatan_verifikasi']): ?>
                            <p class="text-muted small mb-1">Catatan: <?= e($dok['catatan_verifikasi']) ?></p>
                        <?php endif; ?>
                        <?php if ($dok['file_url']): ?>
                            <p class="mb-1">
                                <a href="/admin/pendaftar/<?= e((string) $id) ?>/dokumen/<?= e($dok['jenis']) ?>/lihat" target="_blank" class="small">Lihat file yang diunggah &rarr;</a>
                            </p>
                        <?php else: ?>
                            <p class="text-muted small mb-1">Belum ada file yang diunggah.</p>
                        <?php endif; ?>
                        <form method="post" action="/admin/pendaftar/<?= e((string) $id) ?>/dokumen/<?= e($dok['jenis']) ?>" class="d-flex gap-2">
                            <input type="text" name="catatan" class="form-control form-control-sm" placeholder="Catatan (opsional, untuk penolakan)">
                            <button type="submit" name="status" value="terverifikasi" class="btn btn-sm btn-success">Verifikasi</button>
                            <button type="submit" name="status" value="ditolak" class="btn btn-sm btn-danger">Tolak</button>
                        </form>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 card-title mb-3">Jadwal Wawancara &amp; Seleksi</h2>
                <?php if (!$bolehJadwalkanWawancara): ?>
                    <p class="text-muted small">Semua dokumen harus terverifikasi sebelum jadwal dapat ditentukan.</p>
                <?php else: ?>
                    <form method="post" action="/admin/pendaftar/<?= e((string) $id) ?>/wawancara" class="row g-2 mb-3">
                        <div class="col-4">
                            <input type="date" name="tanggal" class="form-control form-control-sm"
                                   value="<?= e($detail['wawancara']['tanggal'] ?? '') ?>">
                        </div>
                        <div class="col-3">
                            <input type="time" name="jam" class="form-control form-control-sm"
                                   value="<?= e($detail['wawancara'] ? substr($detail['wawancara']['jam'], 0, 5) : '09:00') ?>">
                        </div>
                        <div class="col-3">
                            <input type="text" name="lokasi" class="form-control form-control-sm" placeholder="Lokasi"
                                   value="<?= e($detail['wawancara']['lokasi'] ?? '') ?>">
                        </div>
                        <div class="col-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100">Simpan</button>
                        </div>
                    </form>
                <?php endif; ?>

                <?php if ($detail['hasil_seleksi'] !== null): ?>
                    <p class="mb-2">Hasil ditetapkan: <strong><?= e(hasil_label($detail['hasil_seleksi']['hasil'])) ?></strong></p>
                <?php endif; ?>

                <form method="post" action="/admin/pendaftar/<?= e((string) $id) ?>/hasil" class="d-flex gap-2">
                    <select name="hasil" class="form-select form-select-sm">
                        <option value="diterima">Diterima</option>
                        <option value="tidak_diterima">Tidak Diterima</option>
                        <option value="cadangan">Cadangan</option>
                    </select>
                    <button type="submit" class="btn btn-sm btn-primary">Tetapkan Hasil</button>
                </form>
            </div>
        </div>

        <?php if ($detail['pembayaran'] !== null): ?>
        <div class="card mb-4">
            <div class="card-body">
                <h2 class="h6 card-title mb-3">Pembayaran</h2>
                <p class="mb-2">
                    Tagihan: Rp<?= number_format((float) $detail['pembayaran']['jumlah_tagihan'], 0, ',', '.') ?>
                    — Status: <span class="badge bg-<?= $detail['pembayaran']['status'] === 'lunas' ? 'success' : 'warning' ?>">
                        <?= e(pembayaran_label($detail['pembayaran']['status'])) ?>
                    </span>
                </p>
                <?php if ($detail['pembayaran']['status'] !== 'lunas'): ?>
                    <form method="post" action="/admin/pendaftar/<?= e((string) $id) ?>/pembayaran/lunas">
                        <button type="submit" class="btn btn-sm btn-success">Tandai Lunas</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>
