<?php
/** @var array<string, string> $errors */
/** @var string|null $nomor */
/** @var string|null $nik */
?>

<h1 class="h3 mb-4">Cek Status Pendaftaran</h1>

<?php if (!empty($errors['umum'])): ?>
    <div class="alert alert-danger"><?= e($errors['umum']) ?></div>
<?php endif; ?>

<form method="post" action="/status" novalidate style="max-width: 480px;">
    <div class="mb-3">
        <label class="form-label">Nomor Pendaftaran</label>
        <input type="text" name="nomor" class="form-control <?= isset($errors['nomor']) ? 'is-invalid' : '' ?>"
               placeholder="PPDB-2026-XXXXXX" value="<?= e($nomor ?? '') ?>">
        <?php if (isset($errors['nomor'])): ?><div class="invalid-feedback"><?= e($errors['nomor']) ?></div><?php endif; ?>
    </div>
    <div class="mb-3">
        <label class="form-label">NIK Orang Tua/Wali</label>
        <input type="text" name="nik" maxlength="16" class="form-control <?= isset($errors['nik']) ? 'is-invalid' : '' ?>"
               placeholder="16 digit NIK" value="<?= e($nik ?? '') ?>">
        <?php if (isset($errors['nik'])): ?><div class="invalid-feedback"><?= e($errors['nik']) ?></div><?php endif; ?>
    </div>
    <button type="submit" class="btn btn-primary">Cek Status</button>
</form>
