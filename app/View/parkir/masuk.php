<h1 class="h3 mb-4">Parkir Masuk</h1>

<div class="row justify-content-center">
    <div class="col-lg-6">
        <div class="card shadow-sm">
            <div class="card-body">
                <h5 class="card-title">Catat Parkir Masuk</h5>
                <?php if (!empty($model['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <?= htmlspecialchars($model['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                <?php endif; ?>
                <?php $old = $model['old'] ?? []; ?>
                <form action="/parkir-masuk" method="post" data-confirm="Catat kendaraan {plat} masuk parkir?">
                    <div class="mb-3">
                        <label for="nomor_plat" class="form-label">Nomor Plat</label>
                        <input type="text" class="form-control text-uppercase" id="nomor_plat" name="nomor_plat"
                               value="<?= htmlspecialchars($old['nomor_plat'] ?? '') ?>" required autofocus
                               placeholder="Contoh: B 1234 CD">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis Kendaraan</label>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_kendaraan" id="roda_2" value="roda_2"
                                   <?= (($old['jenis_kendaraan'] ?? '') === 'roda_2') ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="roda_2">Roda 2 (Motor)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="radio" name="jenis_kendaraan" id="roda_4" value="roda_4"
                                   <?= (($old['jenis_kendaraan'] ?? '') === 'roda_4') ? 'checked' : '' ?> required>
                            <label class="form-check-label" for="roda_4">Roda 4 (Mobil)</label>
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Catat Parkir Masuk</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php $tarif = $model['tarif'] ?? []; ?>
<div class="row justify-content-center mt-3">
    <div class="col-lg-6">
        <div class="small text-muted">Tarif berlaku:</div>
        <div class="small">
            Motor: Rp <?= number_format($tarif['roda_2']['pertama'] ?? 0, 0, ',', '.') ?> / jam pertama, + Rp <?= number_format($tarif['roda_2']['per_jam'] ?? 0, 0, ',', '.') ?> / jam<br>
            Mobil: Rp <?= number_format($tarif['roda_4']['pertama'] ?? 0, 0, ',', '.') ?> / jam pertama, + Rp <?= number_format($tarif['roda_4']['per_jam'] ?? 0, 0, ',', '.') ?> / jam
        </div>
    </div>
</div>