<h1 class="h3 mb-4">Dashboard</h1>

<?php $stats = $model['stats'] ?? []; ?>

<div class="row g-3">
    <div class="col-md-3 col-6">
        <div class="card text-bg-primary">
            <div class="card-body">
                <h5 class="card-title">Parkir Hari Ini</h5>
                <p class="display-6 mb-0"><?= (int) ($stats['totalHariIni'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-bg-success">
            <div class="card-body">
                <h5 class="card-title">Motor Keluar</h5>
                <p class="display-6 mb-0"><?= (int) ($stats['motorKeluar'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-bg-warning">
            <div class="card-body">
                <h5 class="card-title">Mobil Keluar</h5>
                <p class="display-6 mb-0"><?= (int) ($stats['mobilKeluar'] ?? 0) ?></p>
            </div>
        </div>
    </div>
    <div class="col-md-3 col-6">
        <div class="card text-bg-danger">
            <div class="card-body">
                <h5 class="card-title">Masih Parkir</h5>
                <p class="display-6 mb-0"><?= (int) ($stats['masihParkir'] ?? 0) ?></p>
            </div>
        </div>
    </div>
</div>

<div class="mt-4 d-flex gap-2">
    <a href="/parkir-masuk" class="btn btn-primary">Parkir Masuk</a>
    <a href="/parkir-keluar" class="btn btn-success">Parkir Keluar</a>
    <a href="/laporan" class="btn btn-outline-secondary">Lihat Laporan</a>
</div>