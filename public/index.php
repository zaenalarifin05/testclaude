<?php

require dirname(__DIR__) . '/src/Core/Autoloader.php';
require dirname(__DIR__) . '/src/Core/helpers.php';

use App\Controllers\AdminController;
use App\Controllers\AuthController;
use App\Controllers\PendaftaranController;
use App\Controllers\StatusController;
use App\Core\Autoloader;
use App\Core\Router;

Autoloader::register();

$router = new Router();

$router->get('/pendaftaran', [PendaftaranController::class, 'form']);
$router->post('/pendaftaran', [PendaftaranController::class, 'simpan']);
$router->get('/pendaftaran/berhasil', [PendaftaranController::class, 'berhasil']);

$router->get('/status', [StatusController::class, 'form']);
$router->post('/status', [StatusController::class, 'cari']);
$router->post('/status/dokumen/{jenis}', [StatusController::class, 'unggahDokumen']);

$router->get('/admin/login', [AuthController::class, 'form']);
$router->post('/admin/login', [AuthController::class, 'login']);
$router->post('/admin/logout', [AuthController::class, 'logout']);

$router->get('/admin', [AdminController::class, 'index']);
$router->get('/admin/pendaftar/{id}', [AdminController::class, 'detail']);
$router->post('/admin/pendaftar/{id}/dokumen/{jenis}', [AdminController::class, 'verifikasiDokumen']);
$router->post('/admin/pendaftar/{id}/wawancara', [AdminController::class, 'jadwalkanWawancara']);
$router->post('/admin/pendaftar/{id}/hasil', [AdminController::class, 'tetapkanHasil']);
$router->post('/admin/pendaftar/{id}/pembayaran/lunas', [AdminController::class, 'konfirmasiLunas']);
$router->get('/admin/pendaftar/{id}/dokumen/{jenis}/lihat', [AdminController::class, 'lihatDokumen']);

$router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
