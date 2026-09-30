<?php

use UCrazy\AplikasiPengelolaanParkirKendaraan\App\Router;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Config\Database;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Controller\HomeController;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Controller\ParkirController;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Controller\UserController;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Middleware\MustLoginMiddleware;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Middleware\MustNotLoginMiddleware;

require_once __DIR__ . "/../vendor/autoload.php";

date_default_timezone_set('Asia/Jakarta');

session_start();

Database::getConnection();

// Home
Router::add('GET', '/', HomeController::class, 'index', []);
Router::add('GET', '/dashboard', HomeController::class, 'dashboard', [MustLoginMiddleware::class]);

// User
Router::add('GET', '/login', UserController::class, 'login', [MustNotLoginMiddleware::class]);
Router::add('POST', '/login', UserController::class, 'postLogin', []);
Router::add('GET', '/logout', UserController::class, 'logout', [MustLoginMiddleware::class]);

// Parkir
Router::add('GET', '/parkir-masuk', ParkirController::class, 'masuk', [MustLoginMiddleware::class]);
Router::add('POST', '/parkir-masuk', ParkirController::class, 'postMasuk', [MustLoginMiddleware::class]);
Router::add('GET', '/parkir-keluar', ParkirController::class, 'keluar', [MustLoginMiddleware::class]);
Router::add('POST', '/parkir-keluar', ParkirController::class, 'postKeluar', [MustLoginMiddleware::class]);
Router::add('GET', '/laporan', ParkirController::class, 'laporan', [MustLoginMiddleware::class]);
Router::add('GET', '/laporan/pdf', ParkirController::class, 'laporanPdf', [MustLoginMiddleware::class]);
Router::add('GET', '/laporan/excel', ParkirController::class, 'laporanExcel', [MustLoginMiddleware::class]);
Router::add('GET', '/laporan/csv', ParkirController::class, 'laporanCsv', [MustLoginMiddleware::class]);

Router::run();