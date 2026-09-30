<h1 class="h3 mb-4">Parkir Keluar</h1>

<p class="text-muted">
    Klik tombol <strong>Keluar</strong> untuk mengeluarkan kendaraan.
    Harga dihitung otomatis berdasarkan durasi parkir.
</p>

<?php
$tarif = $model['tarif'] ?? [];
$kendaraanParkir = $model['kendaraanParkir'] ?? [];
?>

<div class="row row-cols-1 row-cols-md-4 g-3 mb-4">
    <div class="col">
        <div class="card text-bg-secondary">
            <div class="card-body py-2">
                <small>Motor (roda 2)</small>
                <div class="fw-bold">Rp <?= number_format($tarif['roda_2']['pertama'] ?? 0, 0, ',', '.') ?> / jam
                    pertama</div>
                <small>+ Rp <?= number_format($tarif['roda_2']['per_jam'] ?? 0, 0, ',', '.') ?> / jam berikutnya</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card text-bg-secondary">
            <div class="card-body py-2">
                <small>Mobil (roda 4)</small>
                <div class="fw-bold">Rp <?= number_format($tarif['roda_4']['pertama'] ?? 0, 0, ',', '.') ?> / jam
                    pertama</div>
                <small>+ Rp <?= number_format($tarif['roda_4']['per_jam'] ?? 0, 0, ',', '.') ?> / jam berikutnya</small>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card text-bg-dark">
            <div class="card-body py-2">
                <small>Cari Kendaraan</small>
                <form method="get" action="/parkir-keluar" class="d-flex gap-1 mt-1">
                    <input type="text" class="form-control form-control-sm text-uppercase" name="cari"
                        placeholder="Nomor plat..." value="<?= htmlspecialchars($model['cari'] ?? '') ?>">
                    <button class="btn btn-sm btn-light" type="submit">Cari</button>
                    <?php if (!empty($model['cari'])): ?>
                        <a href="/parkir-keluar" class="btn btn-sm btn-outline-light">Reset</a>
                    <?php endif; ?>
                </form>
            </div>
        </div>
    </div>
</div>

<?php if (isset($model['cari']) && trim($model['cari']) !== ''): ?>
    <?php $hasil = $model['daftar'] ?? []; ?>
    <?php if (empty($hasil)): ?>
        <div class="alert alert-info">Kendaraan tidak ditemukan.</div>
    <?php else: ?>
        <div class="card shadow-sm mb-4">
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
                            <th class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($hasil as $i => $parkir): ?>
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
                                <td class="text-end">
                                    <?php if ($parkir->status === 'parkir'): ?>
                                        <form action="/parkir-keluar" method="post"
                                              data-confirm="Keluarkan kendaraan <?= htmlspecialchars($parkir->nomor_plat) ?>?">
                                            <input type="hidden" name="id" value="<?= (int) $parkir->id ?>">
                                            <button type="submit" class="btn btn-success btn-sm">Keluar</button>
                                        </form>
                                    <?php else: ?>
                                        <span class="text-muted">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>

<h5 class="mt-4 mb-2">Kendaraan Sedang Parkir</h5>

<?php if (empty($kendaraanParkir)): ?>
    <div class="alert alert-info">Tidak ada kendaraan yang sedang parkir.</div>
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
                        <th class="text-end">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($kendaraanParkir as $i => $parkir): ?>
                        <tr>
                            <td><?= $i + 1 ?></td>
                            <td class="fw-bold"><?= htmlspecialchars($parkir->nomor_plat) ?></td>
                            <td><?= $parkir->jenis_kendaraan === 'roda_2' ? 'Motor' : 'Mobil' ?></td>
                            <td><?= htmlspecialchars($parkir->waktu_masuk) ?></td>
                            <td class="text-end">
                                <form action="/parkir-keluar" method="post"
                                    data-confirm="Keluarkan kendaraan <?= htmlspecialchars($parkir->nomor_plat) ?>?">
                                    <input type="hidden" name="id" value="<?= (int) $parkir->id ?>">
                                    <button type="submit" class="btn btn-success btn-sm">Keluar</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>