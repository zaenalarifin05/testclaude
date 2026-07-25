<?php
/** @var array|null $gelombang */
/** @var array<string, string> $errors */
/** @var array $old */
/** @var array $jenjangTujuan */
/** @var array $jenjangAsal */

$alamatSama = isset($old['alamat_sama_dengan_ortu']) || $old === [];
?>

<h1 class="h3 mb-1">Formulir Pendaftaran PPDB</h1>

<?php if (!empty($errors['gelombang'])): ?>
    <div class="alert alert-warning"><?= e($errors['gelombang']) ?></div>
<?php elseif ($gelombang !== null): ?>
    <p class="text-muted mb-4">
        Gelombang aktif: <strong><?= e($gelombang['label']) ?></strong>
        (hingga <?= e($gelombang['tanggal_selesai']) ?>)
    </p>
<?php endif; ?>

<?php if ($gelombang !== null): ?>
<form method="post" action="/pendaftaran" novalidate>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 card-title mb-3">Data Orang Tua / Wali</h2>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap Orang Tua/Wali</label>
                <input type="text" name="p_nama" class="form-control <?= isset($errors['p_nama']) ? 'is-invalid' : '' ?>"
                       value="<?= e($old['p_nama'] ?? '') ?>">
                <?php if (isset($errors['p_nama'])): ?><div class="invalid-feedback"><?= e($errors['p_nama']) ?></div><?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">NIK (16 digit)</label>
                    <input type="text" name="p_nik" maxlength="16" class="form-control <?= isset($errors['p_nik']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['p_nik'] ?? '') ?>">
                    <?php if (isset($errors['p_nik'])): ?><div class="invalid-feedback"><?= e($errors['p_nik']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Hubungan dengan Calon Siswa</label>
                    <select name="p_hubungan" class="form-select <?= isset($errors['p_hubungan']) ? 'is-invalid' : '' ?>">
                        <option value="">— Pilih —</option>
                        <?php foreach (['ayah' => 'Ayah', 'ibu' => 'Ibu', 'wali' => 'Wali'] as $val => $label): ?>
                            <option value="<?= e($val) ?>" <?= ($old['p_hubungan'] ?? '') === $val ? 'selected' : '' ?>><?= e($label) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['p_hubungan'])): ?><div class="invalid-feedback"><?= e($errors['p_hubungan']) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">No. HP / WhatsApp Aktif</label>
                    <input type="tel" name="p_hp" class="form-control <?= isset($errors['p_hp']) ? 'is-invalid' : '' ?>"
                           placeholder="08xxxxxxxxxx" value="<?= e($old['p_hp'] ?? '') ?>">
                    <?php if (isset($errors['p_hp'])): ?><div class="invalid-feedback"><?= e($errors['p_hp']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Email Aktif</label>
                    <input type="email" name="p_email" class="form-control <?= isset($errors['p_email']) ? 'is-invalid' : '' ?>"
                           placeholder="nama@email.com" value="<?= e($old['p_email'] ?? '') ?>">
                    <?php if (isset($errors['p_email'])): ?><div class="invalid-feedback"><?= e($errors['p_email']) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="mb-0">
                <label class="form-label">Alamat Lengkap</label>
                <textarea name="p_alamat" rows="2" class="form-control <?= isset($errors['p_alamat']) ? 'is-invalid' : '' ?>"><?= e($old['p_alamat'] ?? '') ?></textarea>
                <?php if (isset($errors['p_alamat'])): ?><div class="invalid-feedback"><?= e($errors['p_alamat']) ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 card-title mb-3">Data Calon Siswa</h2>

            <div class="mb-3">
                <label class="form-label">Nama Lengkap Calon Siswa</label>
                <input type="text" name="s_nama" class="form-control <?= isset($errors['s_nama']) ? 'is-invalid' : '' ?>"
                       value="<?= e($old['s_nama'] ?? '') ?>">
                <?php if (isset($errors['s_nama'])): ?><div class="invalid-feedback"><?= e($errors['s_nama']) ?></div><?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Jenjang yang Dituju</label>
                    <select name="s_jenjang" class="form-select <?= isset($errors['s_jenjang']) ? 'is-invalid' : '' ?>">
                        <option value="">— Pilih —</option>
                        <?php foreach ($jenjangTujuan as $j): ?>
                            <option value="<?= e($j) ?>" <?= ($old['s_jenjang'] ?? '') === $j ? 'selected' : '' ?>><?= e($j) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['s_jenjang'])): ?><div class="invalid-feedback"><?= e($errors['s_jenjang']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tempat Lahir</label>
                    <input type="text" name="s_tempat" class="form-control <?= isset($errors['s_tempat']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['s_tempat'] ?? '') ?>">
                    <?php if (isset($errors['s_tempat'])): ?><div class="invalid-feedback"><?= e($errors['s_tempat']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Tanggal Lahir</label>
                    <input type="date" name="s_tgl" class="form-control <?= isset($errors['s_tgl']) ? 'is-invalid' : '' ?>"
                           value="<?= e($old['s_tgl'] ?? '') ?>">
                    <?php if (isset($errors['s_tgl'])): ?><div class="invalid-feedback"><?= e($errors['s_tgl']) ?></div><?php endif; ?>
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenis Kelamin</label>
                    <select name="s_jk" class="form-select <?= isset($errors['s_jk']) ? 'is-invalid' : '' ?>">
                        <option value="">— Pilih —</option>
                        <option value="L" <?= ($old['s_jk'] ?? '') === 'L' ? 'selected' : '' ?>>Laki-laki</option>
                        <option value="P" <?= ($old['s_jk'] ?? '') === 'P' ? 'selected' : '' ?>>Perempuan</option>
                    </select>
                    <?php if (isset($errors['s_jk'])): ?><div class="invalid-feedback"><?= e($errors['s_jk']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">NISN (opsional)</label>
                    <input type="text" name="s_nisn" class="form-control" value="<?= e($old['s_nisn'] ?? '') ?>">
                </div>
            </div>

            <div class="form-check mb-2">
                <input type="checkbox" name="alamat_sama_dengan_ortu" id="alamat-sama" class="form-check-input"
                       <?= $alamatSama ? 'checked' : '' ?>>
                <label class="form-check-label" for="alamat-sama">Alamat calon siswa sama dengan alamat orang tua</label>
            </div>

            <div class="mb-0" id="wrap-s-alamat" style="<?= $alamatSama ? 'display:none;' : '' ?>">
                <label class="form-label">Alamat Calon Siswa</label>
                <textarea name="s_alamat" rows="2" class="form-control <?= isset($errors['s_alamat']) ? 'is-invalid' : '' ?>"><?= e($old['s_alamat'] ?? '') ?></textarea>
                <?php if (isset($errors['s_alamat'])): ?><div class="invalid-feedback"><?= e($errors['s_alamat']) ?></div><?php endif; ?>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-body">
            <h2 class="h5 card-title mb-3">Sekolah Asal</h2>

            <div class="mb-3">
                <label class="form-label">Nama Sekolah Asal</label>
                <input type="text" name="sc_nama" class="form-control <?= isset($errors['sc_nama']) ? 'is-invalid' : '' ?>"
                       value="<?= e($old['sc_nama'] ?? '') ?>">
                <?php if (isset($errors['sc_nama'])): ?><div class="invalid-feedback"><?= e($errors['sc_nama']) ?></div><?php endif; ?>
            </div>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Jenjang Sekolah Asal</label>
                    <select name="sc_jenjang" class="form-select <?= isset($errors['sc_jenjang']) ? 'is-invalid' : '' ?>">
                        <option value="">— Pilih —</option>
                        <?php foreach ($jenjangAsal as $j): ?>
                            <option value="<?= e($j) ?>" <?= ($old['sc_jenjang'] ?? '') === $j ? 'selected' : '' ?>><?= e($j) ?></option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (isset($errors['sc_jenjang'])): ?><div class="invalid-feedback"><?= e($errors['sc_jenjang']) ?></div><?php endif; ?>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Tahun Lulus / Perkiraan Lulus</label>
                    <input type="text" name="sc_tahun" class="form-control <?= isset($errors['sc_tahun']) ? 'is-invalid' : '' ?>"
                           placeholder="2026" value="<?= e($old['sc_tahun'] ?? '') ?>">
                    <?php if (isset($errors['sc_tahun'])): ?><div class="invalid-feedback"><?= e($errors['sc_tahun']) ?></div><?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-primary btn-lg">Kirim Pendaftaran</button>
</form>
<?php endif; ?>

<script src="/assets/js/pendaftaran.js"></script>
