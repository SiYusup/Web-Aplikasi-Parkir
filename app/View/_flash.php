<?php

use UCrazy\AplikasiPengelolaanParkirKendaraan\Service\SessionService;

$sessionService = new SessionService();
$flash = $sessionService->getFlash();

if ($flash !== null && !empty($flash['message'])): ?>
    <div class="alert alert-<?= htmlspecialchars($flash['type'] ?? 'info') ?> alert-dismissible fade show" role="alert"
        data-flash-type="<?= htmlspecialchars($flash['type'] ?? 'info') ?>"
        data-flash-message="<?= htmlspecialchars($flash['message']) ?>">
        <?= htmlspecialchars($flash['message']) ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
<?php endif; ?>