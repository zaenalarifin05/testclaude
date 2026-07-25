<?php
/** @var array|null $sekolah */
/** @var array|null $gelombangAktif */

$namaSekolah = $sekolah['nama'] ?? 'Sekolah Kami';
$alamat = $sekolah['alamat'] ?? '';
$tahunAjaran = $sekolah['tahun_ajaran'] ?? '';
$nomorWa = '6281918336696';

$langkah = [
    ['judul' => 'Isi Formulir Online', 'desc' => 'Lengkapi data orang tua, calon siswa, dan sekolah asal lewat formulir pendaftaran.'],
    ['judul' => 'Unggah Dokumen', 'desc' => 'Unggah KK, Akta Kelahiran, Ijazah/SKL, dan Pas Foto lewat halaman cek status.'],
    ['judul' => 'Verifikasi &amp; Wawancara', 'desc' => 'Panitia memverifikasi dokumen, lalu menjadwalkan wawancara calon siswa.'],
    ['judul' => 'Pengumuman &amp; Daftar Ulang', 'desc' => 'Pantau hasil seleksi dan selesaikan pembayaran lewat halaman cek status.'],
];

$dokumenWajib = [
    'Kartu Keluarga (KK)',
    'Akta Kelahiran',
    'Ijazah / Surat Keterangan Lulus (SKL)',
    'Pas Foto 3x4',
];
?>

<!-- Blok 1: Hero -->
<section class="hero-section py-5">
    <div class="container py-5">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-white">
                <?php if ($tahunAjaran): ?>
                    <span class="badge bg-light text-dark mb-3">Penerimaan Peserta Didik Baru <?= e($tahunAjaran) ?></span>
                <?php endif; ?>
                <h1 class="display-5 fw-bold mb-3">Selamat Datang di <?= e($namaSekolah) ?></h1>
                <p class="lead" style="color: rgba(255,255,255,.85);">
                    Kami percaya setiap anak berhak mendapatkan pendidikan yang membentuk karakter,
                    membekali ilmu, dan menumbuhkan rasa ingin tahu. Bergabunglah bersama kami dan
                    jadilah bagian dari keluarga besar <?= e($namaSekolah) ?> — tempat tumbuh bersama
                    guru dan sahabat yang peduli akan masa depan ananda.
                </p>
                <div class="d-flex flex-wrap gap-3 mt-4">
                    <a href="/pendaftaran" class="btn btn-gold btn-lg">Daftar Sekarang</a>
                    <a href="/status" class="btn btn-outline-light btn-lg">Cek Status Pendaftaran</a>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="hero-photo shadow"></div>
            </div>
        </div>
    </div>
</section>

<!-- Blok 2: Tata Cara & Persyaratan -->
<section class="py-5" style="background: var(--paper);">
    <div class="container py-4">
        <h2 class="text-center fw-bold mb-2">Tata Cara &amp; Persyaratan Pendaftaran</h2>
        <p class="text-center text-muted mb-5">Empat langkah mudah untuk mendaftar sebagai calon siswa baru</p>

        <div class="row g-4 mb-5">
            <?php foreach ($langkah as $i => $l): ?>
                <div class="col-md-6 col-lg-3">
                    <div class="d-flex gap-3">
                        <div class="step-number"><?= $i + 1 ?></div>
                        <div>
                            <h3 class="h6 fw-bold mb-1"><?= $l['judul'] ?></h3>
                            <p class="text-muted small mb-0"><?= $l['desc'] ?></p>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-4">
                <h3 class="h5 fw-bold mb-3">Dokumen yang Perlu Disiapkan</h3>
                <ul class="row row-cols-1 row-cols-md-2 g-2 list-unstyled mb-0">
                    <?php foreach ($dokumenWajib as $d): ?>
                        <li class="col d-flex align-items-center gap-2">
                            <span class="text-success">&#10003;</span> <?= e($d) ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>

        <?php if ($gelombangAktif !== null): ?>
            <div class="alert alert-primary mt-4 text-center mb-0">
                Gelombang <strong><?= e($gelombangAktif['label']) ?></strong> sedang dibuka —
                pendaftaran ditutup <strong><?= e($gelombangAktif['tanggal_selesai']) ?></strong>.
            </div>
        <?php endif; ?>
    </div>
</section>

<!-- Blok 3: Ajakan Pendaftaran -->
<section class="section-cta py-5 text-center text-white">
    <div class="container py-3">
        <h2 class="fw-bold mb-3">Siap Bergabung Bersama Kami?</h2>
        <p class="mb-4" style="color: rgba(255,255,255,.85);">
            Daftarkan ananda sekarang, atau pantau status pendaftaran yang sudah pernah diisi.
        </p>
        <div class="d-flex flex-wrap justify-content-center gap-3">
            <a href="/pendaftaran" class="btn btn-gold btn-lg px-4">Daftar Calon Siswa Baru</a>
            <a href="/status" class="btn btn-outline-light btn-lg px-4">Cek Status Pendaftaran</a>
        </div>
        <p class="mt-4 mb-0">
            <a href="/admin/login" class="link-light small">Panitia / Admin, masuk di sini &rarr;</a>
        </p>
    </div>
</section>

<!-- Blok 4: Kontak & WhatsApp -->
<section class="py-5">
    <div class="container py-3">
        <div class="row g-4 align-items-center">
            <div class="col-md-6">
                <h2 class="fw-bold mb-3">Hubungi Kami</h2>
                <p class="mb-2"><strong><?= e($namaSekolah) ?></strong></p>
                <?php if ($alamat): ?>
                    <p class="mb-2 text-muted"><?= e($alamat) ?></p>
                <?php endif; ?>
                <p class="text-muted mb-0">
                    Ada pertanyaan seputar pendaftaran? Tim kami siap membantu lewat WhatsApp.
                </p>
            </div>
            <div class="col-md-6 text-md-end">
                <a href="https://wa.me/<?= e($nomorWa) ?>" target="_blank" rel="noopener"
                   class="btn btn-whatsapp btn-lg d-inline-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M13.601 2.326A7.85 7.85 0 0 0 7.994 0C3.627 0 .068 3.558.064 7.926c0 1.399.366 2.76 1.057 3.965L0 16l4.204-1.102a7.9 7.9 0 0 0 3.79.965h.004c4.368 0 7.926-3.558 7.93-7.93A7.9 7.9 0 0 0 13.6 2.326zM7.994 14.521a6.6 6.6 0 0 1-3.356-.92l-.24-.144-2.494.654.666-2.433-.156-.251a6.56 6.56 0 0 1-1.007-3.505c0-3.626 2.957-6.584 6.591-6.584a6.56 6.56 0 0 1 4.66 1.931 6.56 6.56 0 0 1 1.928 4.66c-.004 3.639-2.961 6.592-6.592 6.592m3.615-4.934c-.197-.099-1.17-.578-1.353-.646-.182-.065-.315-.099-.445.099-.133.197-.513.646-.628.775-.114.133-.232.148-.43.05-.197-.1-.836-.308-1.592-.985-.588-.525-.985-1.175-1.103-1.372-.114-.198-.011-.304.088-.403.087-.088.197-.232.296-.346.1-.114.133-.198.198-.33.065-.134.034-.248-.015-.347-.05-.099-.445-1.076-.612-1.47-.16-.389-.323-.335-.445-.34-.114-.007-.247-.007-.38-.007a.73.73 0 0 0-.529.247c-.182.198-.691.677-.691 1.654 0 .977.71 1.916.81 2.049.098.133 1.394 2.132 3.383 2.992.47.205.84.326 1.129.418.475.152.904.129 1.246.08.38-.058 1.171-.48 1.336-.943.164-.464.164-.86.114-.943-.049-.084-.182-.133-.38-.232"/>
                    </svg>
                    Chat via WhatsApp
                </a>
            </div>
        </div>
    </div>
</section>
