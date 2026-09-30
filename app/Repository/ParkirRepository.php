<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Repository;

use UCrazy\AplikasiPengelolaanParkirKendaraan\Config\Database;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Domain\Parkir;

class ParkirRepository
{
    private \PDO $connection;

    public function __construct()
    {
        $this->connection = Database::getConnection();
    }

    public function add(Parkir $parkir): Parkir
    {
        $statement = $this->connection->prepare(
            "INSERT INTO tb_parkir(nomor_plat, jenis_kendaraan, status, deleted_at) VALUES (?, ?, ?, NULL)"
        );
        $statement->execute([
            $parkir->nomor_plat,
            $parkir->jenis_kendaraan,
            $parkir->status
        ]);

        $parkir->id = (int) $this->connection->lastInsertId();
        return $parkir;
    }

    public function update(Parkir $parkir): Parkir
    {
        $statement = $this->connection->prepare(
            "UPDATE tb_parkir SET status = ?, total_bayar = ?, waktu_keluar = ? WHERE id = ?"
        );
        $statement->execute([
            $parkir->status,
            $parkir->total_bayar,
            $parkir->waktu_keluar,
            $parkir->id
        ]);

        return $parkir;
    }

    public function findActiveByNomorPlat(string $nomor_plat): ?Parkir
    {
        $statement = $this->connection->prepare(
            "SELECT id, nomor_plat, jenis_kendaraan, waktu_masuk, waktu_keluar, status, total_bayar
             FROM tb_parkir
             WHERE nomor_plat = ? AND status = 'parkir' AND deleted_at IS NULL
             ORDER BY id DESC LIMIT 1"
        );
        $statement->execute([$nomor_plat]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row ? $this->hydrate($row) : null;
    }

    public function findById(int $id): ?Parkir
    {
        $statement = $this->connection->prepare(
            "SELECT id, nomor_plat, jenis_kendaraan, waktu_masuk, waktu_keluar, status, total_bayar
             FROM tb_parkir WHERE id = ? AND deleted_at IS NULL"
        );
        $statement->execute([$id]);

        $row = $statement->fetch(\PDO::FETCH_ASSOC);
        return $row ? $this->hydrate($row) : null;
    }

    public function findAllActive(): array
    {
        $statement = $this->connection->query(
            "SELECT id, nomor_plat, jenis_kendaraan, waktu_masuk, waktu_keluar, status, total_bayar
             FROM tb_parkir
             WHERE status = 'parkir' AND deleted_at IS NULL
             ORDER BY waktu_masuk ASC"
        );

        $result = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function findAll(?string $tanggal = null, ?string $cari = null): array
    {
        $sql = "SELECT id, nomor_plat, jenis_kendaraan, waktu_masuk, waktu_keluar, status, total_bayar
                FROM tb_parkir WHERE deleted_at IS NULL";
        $params = [];

        if ($tanggal !== null && trim($tanggal) !== '') {
            $sql .= " AND DATE(waktu_masuk) = ?";
            $params[] = $tanggal;
        }

        if ($cari !== null && trim($cari) !== '') {
            $sql .= " AND nomor_plat LIKE ?";
            $params[] = '%' . $cari . '%';
        }

        $sql .= " ORDER BY waktu_masuk DESC";

        $statement = $this->connection->prepare($sql);
        $statement->execute($params);

        $result = [];
        foreach ($statement->fetchAll(\PDO::FETCH_ASSOC) as $row) {
            $result[] = $this->hydrate($row);
        }

        return $result;
    }

    public function stats(): array
    {
        $today = date('Y-m-d');

        $totalHariIni = $this->connection
            ->query("SELECT COUNT(*) FROM tb_parkir WHERE DATE(waktu_masuk) = '$today' AND deleted_at IS NULL")
            ->fetchColumn();

        $motorKeluar = $this->connection
            ->query("SELECT COUNT(*) FROM tb_parkir WHERE DATE(waktu_keluar) = '$today' AND status = 'selesai' AND jenis_kendaraan = 'roda_2' AND deleted_at IS NULL")
            ->fetchColumn();

        $mobilKeluar = $this->connection
            ->query("SELECT COUNT(*) FROM tb_parkir WHERE DATE(waktu_keluar) = '$today' AND status = 'selesai' AND jenis_kendaraan = 'roda_4' AND deleted_at IS NULL")
            ->fetchColumn();

        $masihParkir = $this->connection
            ->query("SELECT COUNT(*) FROM tb_parkir WHERE status = 'parkir' AND deleted_at IS NULL")
            ->fetchColumn();

        return [
            'totalHariIni' => (int) $totalHariIni,
            'motorKeluar' => (int) $motorKeluar,
            'mobilKeluar' => (int) $mobilKeluar,
            'masihParkir' => (int) $masihParkir
        ];
    }

    private function hydrate(array $row): Parkir
    {
        $parkir = new Parkir();
        $parkir->id = (int) $row['id'];
        $parkir->nomor_plat = $row['nomor_plat'];
        $parkir->jenis_kendaraan = $row['jenis_kendaraan'];
        $parkir->waktu_masuk = $row['waktu_masuk'];
        $parkir->waktu_keluar = $row['waktu_keluar'];
        $parkir->status = $row['status'];
        $parkir->total_bayar = $row['total_bayar'];

        return $parkir;
    }
}