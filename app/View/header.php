<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($model['title'] ?? 'Aplikasi Parkir Kendaraan') ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">
    <?php $currentUser = $model['user'] ?? null; ?>
    <?php if ($currentUser !== null): ?>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="/dashboard">Aplikasi Parkir</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link" href="/dashboard">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="/parkir-masuk">Parkir Masuk</a></li>
                    <li class="nav-item"><a class="nav-link" href="/parkir-keluar">Parkir Keluar</a></li>
                    <li class="nav-item"><a class="nav-link" href="/laporan">Laporan</a></li>
                </ul>
                <span class="navbar-text me-3"><?= htmlspecialchars($currentUser->username ?? '') ?></span>
                <a class="btn btn-outline-light btn-sm" href="/logout" data-confirm="Yakin ingin logout?">Logout</a>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <main class="container py-4">
        <?php include __DIR__ . '/_flash.php'; ?>