<?php
/** @var list<array> $daftar */

$error = $_GET['error'] ?? '';
$pesanError = [
    'diri_sendiri' => 'Tidak bisa menghapus akun yang sedang Anda pakai untuk login.',
    'superadmin_terakhir' => 'Tidak bisa menghapus — ini satu-satunya akun superadmin yang tersisa.',
][$error] ?? null;
?>

<a href="/admin" class="d-inline-block mb-3">&larr; Kembali ke dashboard</a>

<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0">Kelola Akun Admin</h1>
    <a href="/admin/akun/baru" class="btn btn-primary btn-sm">+ Tambah Akun</a>
</div>

<?php if ($pesanError): ?>
    <div class="alert alert-danger"><?= e($pesanError) ?></div>
<?php endif; ?>

<div class="table-responsive">
    <table class="table table-hover align-middle">
        <thead>
            <tr>
                <th>Nama</th>
                <th>Email</th>
                <th>Role</th>
                <th>Dibuat</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($daftar as $akun): ?>
                <tr>
                    <td><?= e($akun['nama']) ?></td>
                    <td><?= e($akun['email']) ?></td>
                    <td><span class="badge bg-<?= $akun['role'] === 'superadmin' ? 'dark' : 'secondary' ?>"><?= e($akun['role']) ?></span></td>
                    <td class="text-muted small"><?= e($akun['created_at']) ?></td>
                    <td class="text-end">
                        <a href="/admin/akun/<?= e((string) $akun['id']) ?>/edit" class="btn btn-sm btn-outline-primary">Edit</a>
                        <form method="post" action="/admin/akun/<?= e((string) $akun['id']) ?>/hapus" class="d-inline"
                              onsubmit="return confirm('Hapus akun ini?');">
                            <button type="submit" class="btn btn-sm btn-outline-danger">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>
