# ERD & Skema Database

> Kembali ke [README](../README.md) · Lihat juga [Daftar Fitur](FITUR.md) · [Alur Data](ALUR-DATA.md)

## Diagram Relasi Entitas

```
┌─────────────────────┐
│      tb_users       │
├─────────────────────┤
│ PK  id        INT   │──┐
│ UQ  username  VARCHAR│  │  relasi level aplikasi
│     password  VARCHAR│  │  (cookie parkir_login = username;
└─────────────────────┘  │   tanpa foreign key di database)
                         │   operator tercatat login
                         ▼   memproses data parkir
┌─────────────────────────────────────────┐
│               tb_parkir                 │
├─────────────────────────────────────────┤
│ PK  id              INT AUTO_INCREMENT  │
│     nomor_plat      VARCHAR             │
│     jenis_kendaraan ENUM roda_2/roda_4  │
│     waktu_masuk     DATETIME (default   │
│                     CURRENT_TIMESTAMP)  │
│     waktu_keluar    DATETIME NULL       │
│     status          ENUM parkir/selesai │
│     total_bayar     INT NULL            │
│     deleted_at      DATETIME NULL       │
│                     (soft-delete)       │
└─────────────────────────────────────────┘
```

**Catatan relasi:** tidak ada foreign key antara kedua tabel. `tb_users` hanya untuk autentikasi; seluruh operasional parkir (masuk/keluar/laporan) membaca-menulis `tb_parkir`. Keterkaitan user ↔ aksi parkir hanya tercatat di level aplikasi (cookie login), bukan di skema.

**Status `tb_parkir`:** `parkir` = kendaraan masih di area; `selesai` = sudah keluar dan `total_bayar` terisi. Semua query SELECT memfilter `deleted_at IS NULL`.

> Skema di bawah **diinferensi dari query di `app/Repository/` dan objek di `app/Domain/`** (repo tidak menyertakan file migrasi/SQL). Tipe kolom dipilih agar sesuai perilaku kode; sesuaikan panjang `VARCHAR` dengan kebutuhan bila perlu.

## Skema SQL Siap Impor

```sql
CREATE DATABASE IF NOT EXISTS db_parkir
  CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE db_parkir;

CREATE TABLE IF NOT EXISTS tb_users (
  id       INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50)  NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS tb_parkir (
  id              INT AUTO_INCREMENT PRIMARY KEY,
  nomor_plat      VARCHAR(20)                     NOT NULL,
  jenis_kendaraan ENUM('roda_2','roda_4')         NOT NULL,
  waktu_masuk     DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  waktu_keluar    DATETIME NULL DEFAULT NULL,
  status          ENUM('parkir','selesai') NOT NULL DEFAULT 'parkir',
  total_bayar     INT NULL DEFAULT NULL,
  deleted_at      DATETIME NULL DEFAULT NULL,
  INDEX idx_parkir_plat_status (nomor_plat, status),
  INDEX idx_parkir_waktu_masuk (waktu_masuk)
) ENGINE=InnoDB;
```

## Data Dummy (untuk mencoba alur di [ALUR-DATA](ALUR-DATA.md))

```sql
-- Password 'admin123' versi hash contoh (ganti dengan hasil password_hash Anda):
INSERT INTO tb_users (username, password)
VALUES ('admin', '$2y$10$CONTOH_GANTI_DENGAN_HASH_ASLI_ANDA');

INSERT INTO tb_parkir (nomor_plat, jenis_kendaraan, waktu_masuk, waktu_keluar, status, total_bayar, deleted_at)
VALUES
  ('B 1234 CD', 'roda_2', '2026-09-30 08:05:00', '2026-09-30 10:15:00', 'selesai', 4000, NULL),
  ('D 5678 EF', 'roda_4', '2026-09-30 09:40:00', '2026-09-30 10:10:00', 'selesai', 5000, NULL),
  ('B 9999 ZZ', 'roda_2', '2026-09-30 11:00:00', NULL, 'parkir', NULL, NULL);
```

Hasil yang diharapkan: laporan memuat 3 baris, total pendapatan Rp 9.000, dan 1 kendaraan masih parkir.
