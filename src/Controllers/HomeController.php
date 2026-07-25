<?php

namespace App\Controllers;

use App\Models\Gelombang;
use App\Models\Sekolah;

class HomeController
{
    public function index(): void
    {
        $sekolah = Sekolah::ambil();
        $gelombangAktif = Gelombang::aktif();
        $fullWidth = true;
        $title = ($sekolah['nama'] ?? 'PPDB') . ' — PPDB';

        require __DIR__ . '/../Views/layout/header.php';
        require __DIR__ . '/../Views/home/index.php';
        require __DIR__ . '/../Views/layout/footer.php';
    }
}
