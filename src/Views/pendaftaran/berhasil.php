<?php
/** @var string $nomorPendaftaran */
?>

<div class="text-center py-5">
    <h1 class="h3 mb-3">Pendaftaran Berhasil Dikirim</h1>
    <p class="text-muted">Simpan nomor pendaftaran berikut untuk memantau status Anda:</p>
    <p class="display-6 fw-bold mb-4"><?= e($nomorPendaftaran) ?></p>
    <a href="/pendaftaran" class="btn btn-outline-primary">Daftar Calon Siswa Lain</a>
</div>
