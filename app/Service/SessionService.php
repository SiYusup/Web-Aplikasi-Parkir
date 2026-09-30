<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Service;


use UCrazy\AplikasiPengelolaanParkirKendaraan\Domain\User;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Repository\UserRepository;

/**
 * Login disimpan langsung di Cookie (tanpa tabel sessions).
 * Cookie: kasir_login = username, expired 1 hari, path /.
 */
class SessionService
{
    public const COOKIE_NAME = 'parkir_login';

    private UserRepository $userRepository;

    public function __construct()
    {
        $this->userRepository = new UserRepository();
    }

    public function create(string $username): void
    {
        // kirim cookie ke browser (harus sebelum output)
        setcookie(self::COOKIE_NAME, $username, time() + 86400, '/');
        // simulasikan langsung agar bisa dibaca di request yang sama / mode test CLI
        $_COOKIE[self::COOKIE_NAME] = $username;
    }

    public function destroy(): void
    {
        setcookie(self::COOKIE_NAME, '', time() - 3600, '/');
        unset($_COOKIE[self::COOKIE_NAME]);
    }

    public function current(): ?User
    {
        $username = $_COOKIE[self::COOKIE_NAME] ?? null;
        if ($username == null || trim($username) == '') {
            return null;
        }
        return $this->userRepository->findByUsername($username);
    }

    public function setFlash(string $type, string $message): void
    {
        $this->ensureSession();
        $_SESSION['flash'] = ['type' => $type, 'message' => $message];
    }

    public function getFlash(): ?array
    {
        $this->ensureSession();
        $flash = $_SESSION['flash'] ?? null;
        unset($_SESSION['flash']);
        return $flash;
    }

    private function ensureSession(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
}
