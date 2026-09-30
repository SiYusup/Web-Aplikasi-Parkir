<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Middleware;

use UCrazy\AplikasiPengelolaanParkirKendaraan\App\View;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Service\SessionService;

class MustLoginMiddleware implements Middleware
{
    function before(): void
    {
        $sessionService = new SessionService();
        $user = $sessionService->current();
        if ($user == null) {
            View::redirect('/login');
        }
    }
}
