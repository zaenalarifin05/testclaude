<?php
/** @var list<array> $daftar */
/** @var list<array> $gelombangList */
/** @var int $totalData */
/** @var int $totalHalaman */
/** @var int $halaman */

$gelombangFilter = $_GET['gelombang'] ?? 'all';
$tahapFilter = $_GET['tahap'] ?? 'all';
$cari = $_GET['cari'] ?? '';

$queryDasar = array_filter([
    'gelombang' => $gelombangFilter !== 'all' ? $gelombangFilter : null,
    'tahap' => $tahapFilter !== 'all' ? $tahapFilter : null,
    'cari' => $cari !== '' ? $cari : null,
], static fn ($v) => $v !== null);

$tautanHalaman = static fn (int $p): string => '/admin?' . http_build_query($queryDasar + ['page' => $p]);
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Panel Admin PPDB</h1>
    <div class="d-flex gap-2">
        <?php if (\App\Core\Auth::isSuperadmin()): ?>
            <a href="/admin/akun" class="btn btn-sm btn-outline-secondary">Kelola Akun</a>
        <?php endif; ?>
        <form method="post" action="/admin/logout" class="mb-0">
            <button type="submit" class="btn btn-sm btn-outline-secondary">Keluar</button>
        </form>
    </div>
</div>

<form method="get" action="/admin" class="row g-2 mb-4">
    <div class="col-auto">
        <select name="gelombang" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all" <?= $gelombangFilter === 'all' ? 'selected' : '' ?>>Semua Gelombang</option>
            <?php foreach ($gelombangList as $g): ?>
                <option value="<?= e($g['kode']) ?>" <?= $gelombangFilter === $g['kode'] ? 'selected' : '' ?>>
                    <?= e($g['label']) ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <select name="tahap" class="form-select form-select-sm" onchange="this.form.submit()">
            <option value="all" <?= $tahapFilter === 'all' ? 'selected' : '' ?>>Semua Tahap</option>
            <?php foreach (semua_tahap() as $t): ?>
                <option value="<?= e($t) ?>" <?= $tahapFilter === $t ? 'selected' : '' ?>><?= e(tahap_label($t)) ?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="col-auto">
        <input type="text" name="cari" class="form-control form-control-sm" placeholder="Cari nama/nomor"
               value="<?= e($cari) ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-sm btn-primary">Filter</button>
    </div>
</form>

<?php if (empty($daftar)): ?>
    <div class="alert alert-secondary">Tidak ada pendaftar yang cocok dengan filter saat ini.</div>
<?php else: ?>
    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>No. Pendaftaran</th>
                        <th>Calon Siswa</th>
                        <th>Jenjang</th>
                        <th>Gelombang</th>
                        <th>Status</th>
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $r): ?>
                        <tr>
                            <td class="font-monospace"><?= e($r['nomor_pendaftaran']) ?></td>
                            <td><?= e($r['nama_siswa']) ?></td>
                            <td><?= e($r['jenjang_tujuan']) ?></td>
                            <td><?= e($r['gelombang_label']) ?></td>
                            <td><span class="badge bg-<?= e(tahap_warna($r['tahap'])) ?>"><?= e(tahap_label($r['tahap'])) ?></span></td>
                            <td class="text-end"><a href="/admin/pendaftar/<?= e((string) $r['id']) ?>" class="btn btn-sm btn-outline-primary">Kelola</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white d-flex justify-content-between align-items-center flex-wrap gap-2">
            <span class="text-muted small">
                Menampilkan <?= e((string) (($halaman - 1) * 10 + 1)) ?>–<?= e((string) (($halaman - 1) * 10 + count($daftar))) ?>
                dari <?= e((string) $totalData) ?> pendaftar
            </span>
            <?php if ($totalHalaman > 1): ?>
                <nav aria-label="Navigasi halaman pendaftar">
                    <ul class="pagination pagination-sm mb-0">
                        <li class="page-item <?= $halaman <= 1 ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e($tautanHalaman(max(1, $halaman - 1))) ?>">&laquo;</a>
                        </li>
                        <?php for ($p = 1; $p <= $totalHalaman; $p++): ?>
                            <li class="page-item <?= $p === $halaman ? 'active' : '' ?>">
                                <a class="page-link" href="<?= e($tautanHalaman($p)) ?>"><?= $p ?></a>
                            </li>
                        <?php endfor; ?>
                        <li class="page-item <?= $halaman >= $totalHalaman ? 'disabled' : '' ?>">
                            <a class="page-link" href="<?= e($tautanHalaman(min($totalHalaman, $halaman + 1))) ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
