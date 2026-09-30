<?php

namespace UCrazy\KasirTokoSederhana\Tests\Repository;

use PHPUnit\Framework\TestCase;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Domain\User;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Repository\UserRepository;

class UserRepositoryTest extends TestCase
{
    public UserRepository $userRepository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->userRepository = new UserRepository();
        $this->userRepository->deleteUser();
    }

    public function testUserSaveSuccess(): void
    {
        $user_1 = new User();
        $user_1->username = "Hello World";
        $user_1->password = "Rahasia";

        $result = $this->userRepository->save($user_1);

        self::assertNotNull($result);
        self::assertSame($user_1->username, $result->username);
        self::assertSame($user_1->password, $result->password);
        self::assertSame($user_1->full_name, $result->full_name);
        self::assertSame($user_1->role, $result->role);
    }

    public function testUserUpdateSuccess(): void
    {
        $user_1 = new User();
        $user_1->username = "Halo";
        $user_1->password = "Rahasia";
        $user_1->full_name = "Halo Saya";
        $user_1->role = "pembeli";

        $result_before = $this->userRepository->save($user_1);

        $user_2 = new User();
        $user_2->username = "Halo";
        $user_2->password = "Rahasia";
        $user_2->full_name = "Halo Terupdate";
        $user_2->role = "pembeli";

        $result = $this->userRepository->update($user_2);

        self::assertNotNull($result);
        self::assertNotEquals($result_before->full_name, $result->full_name);
        self::assertEquals("Halo Terupdate", $result->full_name);
    }

    public function testUserUpdatePasswordSuccess(): void
    {
        $user_1 = new User();
        $user_1->username = "Halo";
        $user_1->password = "Rahasia";
        $user_1->full_name = "Halo Saya";
        $user_1->role = "pembeli";

        $result_before = $this->userRepository->save($user_1);

        $user_2 = new User();
        $user_2->username = "Halo";
        $user_2->password = "Rahasia_Banget";
        $user_2->full_name = "Halo Saya";
        $user_2->role = "pembeli";

        $result_after = $this->userRepository->updatePassword($user_2);

        self::assertNotNull($result_after);
        self::assertNotEquals($result_before->password, $result_after->password);
        self::assertEquals("Rahasia_Banget", $result_after->password);
    }

    public function testFindByUsernameSuccess(): void
    {
        $user_1 = new User();
        $user_1->username = "Usyup_12";
        $user_1->password = "Rahasia";
        $user_1->full_name = "Halo Saya";
        $user_1->role = "pembeli";

        $this->userRepository->save($user_1);

        // Cari dengan username yang persis sama
        $result = $this->userRepository->findByUsername("Usyup_12");

        self::assertNotNull($result);
        self::assertEquals("Usyup_12", $result->username);
        self::assertEquals("Halo Saya", $result->full_name);
    }

    public function testFindByUsernameNotFound(): void
    {
        // Test pendukung: memastikan return null jika username tidak ada
        $result = $this->userRepository->findByUsername("UserTidakAda");

        self::assertNull($result);
    }
}