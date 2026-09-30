<h1 class="h3 mb-4">Laporan Parkir</h1>

<form method="get" action="/laporan" class="row g-2 mb-3">
    <div class="col-auto">
        <input type="date" class="form-control" name="tanggal" value="<?= htmlspecialchars($model['tanggal'] ?? '') ?>">
    </div>
    <div class="col-auto">
        <input type="text" class="form-control text-uppercase" name="cari" placeholder="Cari nomor plat..."
            value="<?= htmlspecialchars($model['cari'] ?? '') ?>">
    </div>
    <div class="col-auto">
        <button type="submit" class="btn btn-primary">Cari</button>
    </div>
    <div class="col-auto">
        <a href="/laporan" class="btn btn-outline-secondary">Reset</a>
    </div>
    <!-- <div class="col-auto ms-auto">
        <a href="/laporan/pdf?tanggal=< //urlencode($model['tanggal'] ?? '') &cari= //urlencode($model['cari'] ?? '')"
           class="btn btn-outline-danger">Export PDF</a>
    </div> -->
    <?php
    $exportQuery = http_build_query([
        'tanggal' => $model['tanggal'] ?? '',
        'cari' => $model['cari'] ?? '',
    ]);
    ?>
    <div class="col-auto ms-auto d-flex gap-2">
        <a href="/laporan/pdf?<?= htmlspecialchars($exportQuery) ?>" class="btn btn-outline-danger" data-confirm="Export laporan ke PDF?">Export PDF</a>
        <a href="/laporan/excel?<?= htmlspecialchars($exportQuery) ?>" class="btn btn-outline-success" data-confirm="Export laporan ke Excel?">Export Excel</a>
        <a href="/laporan/csv?<?= htmlspecialchars($exportQuery) ?>" class="btn btn-outline-primary" data-confirm="Export laporan ke CSV?">Export CSV</a>
    </div>
    <div class="col-12">
        <span class="badge text-bg-success p-2">Total Pendapatan: Rp
            <?= number_format((int) $model['totalPendapatan'], 0, ',', '.') ?></span>
        <?php if (!empty($model['tanggal'])): ?>
            <span class="badge text-bg-secondary p-2">Tanggal: <?= htmlspecialchars($model['tanggal']) ?></span>
        <?php endif; ?>
        <?php if (!empty($model['cari'])): ?>
            <span class="badge text-bg-secondary p-2">Plat: <?= htmlspecialchars($model['cari']) ?></span>
        <?php endif; ?>
    </div>
</form>

<?php $daftar = $model['daftar'] ?? []; ?>

<?php if (empty($daftar)): ?>
    <div class="alert alert-info">Belum ada data parkir.</div>
<?php else: ?>
    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Nomor Plat</th>
                        <th>Jenis</th>
                        <th>Waktu Masuk</th>
                        <th>Waktu Keluar</th>
                        <th>Status</th>
                        <th class="text-end">Total Bayar</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($daftar as $i => $parkir): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($parkir->nomor_plat) ?></td>
                            <td><?= $parkir->jenis_kendaraan === 'roda_2' ? 'Motor' : 'Mobil' ?></td>
                            <td><?= htmlspecialchars($parkir->waktu_masuk) ?></td>
                            <td><?= $parkir->waktu_keluar ? htmlspecialchars($parkir->waktu_keluar) : '-' ?></td>
                            <td>
                                <?php if ($parkir->status === 'parkir'): ?>
                                    <span class="badge text-bg-warning">Parkir</span>
                                <?php else: ?>
                                    <span class="badge text-bg-success">Selesai</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <?= $parkir->total_bayar !== null ? 'Rp ' . number_format((int) $parkir->total_bayar, 0, ',', '.') : '-' ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>