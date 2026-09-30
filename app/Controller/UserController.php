<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Controller;

use UCrazy\AplikasiPengelolaanParkirKendaraan\App\View;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Exception\ValidationException;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Model\UserLoginRequest;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Repository\UserRepository;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Service\UserService;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Service\SessionService;


class UserController
{
    private UserService $userService;
    private SessionService $sessionService;

    public function __construct()
    {
        $userRepository = new UserRepository();
        $this->userService = new UserService($userRepository);
        $this->sessionService = new SessionService();
    }
    
    public function login(): void
    {
        View::render('User/login', [
            'title' => 'Login - Aplikasi Parkir Kendaraan'
        ]);
    }

    public function postLogin(): void
    {
        $request = new UserLoginRequest();
        $request->username = $_POST['username'] ?? null;
        $request->password = $_POST['password'] ?? null;

        try {
            $response = $this->userService->login($request);
            // simpan login langsung di cookie (tanpa tabel sessions)
            $this->sessionService->create($response->user->username);
            $this->sessionService->setFlash('success', "Login berhasil. Selamat datang, " . $response->user->username . "!");
            View::redirect('/dashboard', []);
        } catch (ValidationException $exception) {
            View::render('User/login', [
                'title' => 'Login - Aplikasi Parkir Kendaraan',
                'error' => $exception->getMessage()
            ]);
        }
    }

    public function logout(): void
    {
        $this->sessionService->destroy();
        View::redirect('/', []);
    }
}
