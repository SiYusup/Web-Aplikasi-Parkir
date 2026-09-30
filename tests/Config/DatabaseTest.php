<?php

use PHPUnit\Framework\TestCase;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Config\Database;

class DatabaseTest extends TestCase
{
    public function testConnection()
    {
        $connection = Database::getConnection();
        self::assertNotNull($connection);
    }

    public function testConnectionSame()
    {
        $connection1 = Database::getConnection();
        $connection2 = Database::getConnection();
        self::assertSame($connection1, $connection2);
    }
}