<?php
/** @var array<string, string> $errors */
?>

<h1 class="h3 mb-4">Login Admin PPDB</h1>

<?php if (!empty($errors['umum'])): ?>
    <div class="alert alert-danger"><?= e($errors['umum']) ?></div>
<?php endif; ?>

<form method="post" action="/admin/login" novalidate style="max-width: 400px;">
    <div class="mb-3">
        <label class="form-label">Email</label>
        <input type="email" name="email" class="form-control" autofocus>
    </div>
    <div class="mb-3">
        <label class="form-label">Kata Sandi</label>
        <input type="password" name="password" class="form-control">
    </div>
    <button type="submit" class="btn btn-primary">Masuk</button>
</form>
