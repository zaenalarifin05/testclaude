<?php
/** @var array $detail */
/** @var string $tahap */
/** @var string $nomor */
/** @var string $nik */
/** @var array{tipe: string, teks: string}|null $pesanUpload */
/** @var list<array> $notifikasi */

$pesanUpload ??= null;
?>

<h1 class="h3 mb-1">Status Pendaftaran</h1>
<p class="text-muted mb-4"><?= e($detail['pendaftar']['nomor_pendaftaran']) ?></p>

<?php if ($pesanUpload !== null): ?>
    <div class="alert alert-<?= e($pesanUpload['tipe']) ?>"><?= e($pesanUpload['teks']) ?></div>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <h2 class="h5 card-title"><?= e($detail['calon_siswa']['nama']) ?></h2>
        <p class="mb-1">Jenjang: <?= e($detail['calon_siswa']['jenjang_tujuan']) ?></p>
        <p class="mb-1">Gelombang: <?= e($detail['gelombang']['label'] ?? '-') ?></p>
        <p class="mb-0">Status saat ini: <span class="badge bg-<?= e(tahap_warna($tahap)) ?>"><?= e(tahap_label($tahap)) ?></span></p>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <h3 class="h6 card-title mb-3">Kelengkapan Dokumen</h3>
        <?php foreach ($detail['dokumen'] as $dok): ?>
            <div class="border-bottom pb-2 mb-2">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <?= e(dokumen_label($dok['jenis'])) ?>
                    <span class="badge bg-<?= e(status_dokumen_warna($dok['status'])) ?>"><?= e(status_dokumen_label($dok['status'])) ?></span>
                </div>
                <?php if ($dok['catatan_verifikasi']): ?>
                    <p class="text-danger small mb-1">Catatan panitia: <?= e($dok['catatan_verifikasi']) ?></p>
                <?php endif; ?>
                <?php if ($dok['status'] !== 'terverifikasi'): ?>
                    <form method="post" action="/status/dokumen/<?= e($dok['jenis']) ?>" enctype="multipart/form-data" class="d-flex gap-2">
                        <input type="hidden" name="nomor" value="<?= e($nomor) ?>">
                        <input type="hidden" name="nik" value="<?= e($nik) ?>">
                        <input type="file" name="file" accept=".pdf,.jpg,.jpeg,.png" class="form-control form-control-sm">
                        <button type="submit" class="btn btn-sm btn-primary text-nowrap">Unggah</button>
                    </form>
                <?php endif; ?>
            </div>
        <?php endforeach; ?>
        <p class="text-muted small mb-0">Format PDF/JPG/PNG, maksimal 2MB.</p>
    </div>
</div>

<?php if ($detail['wawancara'] !== null): ?>
<div class="card mb-4">
    <div class="card-body">
        <h3 class="h6 card-title mb-2">Jadwal Wawancara</h3>
        <p class="mb-0">
            <?= e($detail['wawancara']['tanggal']) ?> pukul <?= e(substr($detail['wawancara']['jam'], 0, 5)) ?> WIB
            di <?= e($detail['wawancara']['lokasi']) ?>
        </p>
    </div>
</div>
<?php endif; ?>

<?php if ($detail['hasil_seleksi'] !== null): ?>
<div class="card mb-4">
    <div class="card-body">
        <h3 class="h6 card-title mb-2">Hasil Seleksi</h3>
        <p class="mb-0"><?= e(hasil_label($detail['hasil_seleksi']['hasil'])) ?></p>
    </div>
</div>
<?php endif; ?>

<?php if ($detail['pembayaran'] !== null): ?>
<div class="card mb-4">
    <div class="card-body">
        <h3 class="h6 card-title mb-2">Pembayaran</h3>
        <p class="mb-0">
            Tagihan: Rp<?= number_format((float) $detail['pembayaran']['jumlah_tagihan'], 0, ',', '.') ?>
            — Status: <?= e(pembayaran_label($detail['pembayaran']['status'])) ?>
        </p>
    </div>
</div>
<?php endif; ?>

<?php if (!empty($notifikasi)): ?>
<div class="card mb-4">
    <div class="card-body">
        <h3 class="h6 card-title mb-3">Riwayat Notifikasi</h3>
        <p class="text-muted small">Notifikasi berikut juga "dikirim" ke email <?= e($detail['orang_tua']['email']) ?>.</p>
        <?php foreach ($notifikasi as $n): ?>
            <div class="border-start border-3 border-primary ps-3 mb-3">
                <div class="d-flex justify-content-between align-items-baseline flex-wrap gap-2">
                    <strong class="small"><?= e($n['judul']) ?></strong>
                    <span class="text-muted small"><?= e($n['dikirim_at']) ?></span>
                </div>
                <p class="mb-0 small text-muted"><?= e($n['pesan']) ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<a href="/status" class="btn btn-outline-secondary">Cek Pendaftaran Lain</a>
