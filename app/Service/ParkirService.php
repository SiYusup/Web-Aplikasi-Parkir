<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Service;

use UCrazy\AplikasiPengelolaanParkirKendaraan\Domain\Parkir;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Exception\ValidationException;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Model\ParkirMasukRequest;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Model\ParkirKeluarRequest;
use UCrazy\AplikasiPengelolaanParkirKendaraan\Repository\ParkirRepository;

class ParkirService
{
    private const TARIF = [
        'roda_2' => ['pertama' => 2000, 'per_jam' => 1000],
        'roda_4' => ['pertama' => 5000, 'per_jam' => 1000]
    ];

    private ParkirRepository $parkirRepository;

    public static function tarif(): array
    {
        return self::TARIF;
    }

    public function __construct(ParkirRepository $parkirRepository)
    {
        $this->parkirRepository = $parkirRepository;
    }

    public function masuk(ParkirMasukRequest $request): Parkir
    {
        $this->validateMasuk($request);

        $nomorPlat = strtoupper(trim($request->nomor_plat));

        $sudahParkir = $this->parkirRepository->findActiveByNomorPlat($nomorPlat);
        if ($sudahParkir !== null) {
            throw new ValidationException("Kendaraan dengan plat $nomorPlat sudah tercatat parkir.");
        }

        $parkir = new Parkir();
        $parkir->nomor_plat = $nomorPlat;
        $parkir->jenis_kendaraan = $request->jenis_kendaraan;
        $parkir->status = 'parkir';

        return $this->parkirRepository->add($parkir);
    }

    public function keluar(ParkirKeluarRequest $request): Parkir
    {
        $this->validateKeluar($request);

        $parkir = $this->parkirRepository->findById($request->id);
        if ($parkir === null || $parkir->status !== 'parkir') {
            throw new ValidationException("Kendaraan tidak ditemukan atau sudah keluar.");
        }

        $totalBayar = $this->hitungBiaya($parkir);

        $parkir->status = 'selesai';
        $parkir->total_bayar = (string) $totalBayar;
        $parkir->waktu_keluar = date('Y-m-d H:i:s');

        $this->parkirRepository->update($parkir);

        return $parkir;
    }

    private function hitungBiaya(Parkir $parkir): int
    {
        $tarif = self::TARIF[$parkir->jenis_kendaraan] ?? self::TARIF['roda_2'];

        $masuk = new \DateTime($parkir->waktu_masuk);   
        $keluar = new \DateTime();
        $menit = $masuk->diff($keluar);

        $totalMenit = $menit->days * 1440 + $menit->h * 60 + $menit->i + ($menit->s > 0 ? 1 : 0);
        $jam = max(1, (int) ceil($totalMenit / 60));

        return $tarif['pertama'] + (($jam - 1) * $tarif['per_jam']);
    }

    private function validateMasuk(ParkirMasukRequest $request): void
    {
        if ($request->nomor_plat == null || trim($request->nomor_plat) == '') {
            throw new ValidationException("Nomor plat wajib diisi.");
        }

        if (!in_array($request->jenis_kendaraan, ['roda_2', 'roda_4'], true)) {
            throw new ValidationException("Jenis kendaraan wajib dipilih.");
        }
    }

    private function validateKeluar(ParkirKeluarRequest $request): void
    {
        if ($request->id == null || $request->id <= 0) {
            throw new ValidationException("Data kendaraan tidak valid.");
        }
    }
}