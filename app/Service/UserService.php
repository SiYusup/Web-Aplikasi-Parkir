<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Service;

use UCrazy\AplikasiPengelolaanParkirKendaraan\Config\Database;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Domain\User;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Exception\ValidationException;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Model\UserLoginRequest;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Model\UserLoginResponse;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Repository\UserRepository;



class UserService
{
    private UserRepository $userRepository;

    public function __construct(UserRepository $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function login(UserLoginRequest $request): UserLoginResponse
    {
        $this->validateLogin($request);

        $user = $this->userRepository->findByUsername($request->username);
        if ($user == null) {
            throw new ValidationException("Username or password is wrong");
        }

        if (password_verify($request->password, $user->password)) {
            $response = new UserLoginResponse();
            $response->user = $user;
            return $response;
        } else {
            throw new ValidationException("Username or password is wrong");
        }
    }

    private function validateLogin(UserLoginRequest $request): void
    {
        if (
            $request->username == null || $request->password == null ||
            trim($request->username) == "" || trim($request->password) == ""
        ) {
            throw new ValidationException("Username, Password can not blank");
        }
    }

}
