<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Controller;

use UCrazy\AplikasiPengelolaanParkirKendaraan\App\View;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Repository\ParkirRepository;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Service\SessionService;

class HomeController
{
    private SessionService $sessionService;
    private ParkirRepository $parkirRepository;

    public function __construct()
    {
        $this->sessionService = new SessionService();
        $this->parkirRepository = new ParkirRepository();
    }

    public function index()
    {
        $user = $this->sessionService->current();
        if ($user != null) {
            View::redirect('/dashboard', []);
            return;
        }

        View::render("Home/index", [
            "title" => "Selamat Datang - Aplikasi Parkir Kendaraan"
        ]);
    }

    public function dashboard()
    {
        $user = $this->sessionService->current();
        if ($user == null) {
            View::redirect('/login', []);
            return;
        }

        View::render("Home/dashboard", [
            "title" => "Dashboard - Aplikasi Parkir Kendaraan",
            "user" => $user,
            "stats" => $this->parkirRepository->stats()
        ]);
    }
}