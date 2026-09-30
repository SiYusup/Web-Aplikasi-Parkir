<?php

namespace UCrazy\AplikasiPengelolaanParkirKendaraan\Domain;

class Parkir
{
    public ?int $id = null;
    public ?string $nomor_plat = null;
    public ?string $jenis_kendaraan = null;
    public ?string $waktu_masuk = null;
    public ?string $waktu_keluar = null;
    public ?string $status = null;
    public ?string $total_bayar = null;
}
