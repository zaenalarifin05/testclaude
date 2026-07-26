<?php
/** @var array|null $akun */
/** @var array<string, string> $errors */
/** @var array $old */

$editMode = $akun !== null;
$action = $editMode ? '/admin/akun/' . $akun['id'] : '/admin/akun';
?>

<a href="/admin/akun" class="d-inline-block mb-3">&larr; Kembali ke daftar akun</a>

<h1 class="h3 mb-4"><?= $editMode ? 'Edit Akun Admin' : 'Tambah Akun Admin' ?></h1>

<form method="post" action="<?= e($action) ?>" novalidate style="max-width: 480px;">
    <div class="mb-3">
        <label class="form-label">Nama</label>
        <input type="text" name="nama" class="form-control <?= isset($errors['nama']) ? 'is-invalid' : '' ?>"
               value="<?= e($old['nama'] ?? '') ?>">
        <?php if (isset($errors['nama'])): ?><div class="invalid-feedback"><?= e($errors['nama']) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control <?= isset($errors['email']) ? 'is-invalid' : '' ?>"
               value="<?= e($old['email'] ?? '') ?>">
        <?php if (isset($errors['email'])): ?><div class="invalid-feedback"><?= e($errors['email']) ?></div><?php endif; ?>
    </div>

    <div class="mb-3">
        <label class="form-label">Kata Sandi <?= $editMode ? '(kosongkan jika tidak diganti)' : '' ?></label>
        <input type="password" name="password" class="form-control <?= isset($errors['password']) ? 'is-invalid' : '' ?>">
        <?php if (isset($errors['password'])): ?><div class="invalid-feedback"><?= e($errors['password']) ?></div><?php endif; ?>
        <div class="form-text">Minimal 8 karakter.</div>
    </div>

    <div class="mb-4">
        <label class="form-label">Role</label>
        <select name="role" class="form-select <?= isset($errors['role']) ? 'is-invalid' : '' ?>">
            <?php foreach (['panitia' => 'Panitia', 'superadmin' => 'Superadmin'] as $value => $label): ?>
                <option value="<?= e($value) ?>" <?= ($old['role'] ?? '') === $value ? 'selected' : '' ?>><?= e($label) ?></option>
            <?php endforeach; ?>
        </select>
        <?php if (isset($errors['role'])): ?><div class="invalid-feedback"><?= e($errors['role']) ?></div><?php endif; ?>
    </div>

    <button type="submit" class="btn btn-primary"><?= $editMode ? 'Simpan Perubahan' : 'Tambah Akun' ?></button>
</form>
