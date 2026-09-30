<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Repository;

use UCrazy\AplikasiPengelolaanParkirKendaraan\Config\Database;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Domain\User;


class UserRepository
{
    private \PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function save(User $user): User
    {
        $statement = $this->connection->prepare("INSERT INTO tb_users(username, password) VALUES (?, ?)");
        $statement->execute([
            $user->username,
            $user->password,
        ]);
        return $user;
    }

    public function updatePassword(User $user): User
    {
        $statement = $this->connection->prepare("UPDATE tb_users SET password = ? WHERE username = ?");
        $statement->execute([
            $user->password,
            $user->username

        ]);

        return $user;
    }

    public function findByUsername(string $username): ?User
    {
        $statement = $this->connection->prepare("SELECT id, username, password FROM tb_users WHERE username = ?");
        $statement->execute([$username]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);

        // Jika data tidak ditemukan, return null (bukan false)
        if (!$row) {
            return null;
        }

        // Mapping dari array DB ke Object User Domain
        $user = new User();
        $user->id = isset($row['id']) ? (int) $row['id'] : null;
        $user->username = $row['username'];
        $user->password = $row['password'];

        return $user;
    }
    
    public function deleteUser()
    {
        $statement = $this->connection->exec("DELETE FROM tb_users");
    }
}