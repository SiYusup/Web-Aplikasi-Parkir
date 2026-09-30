# Web Aplikasi Parkir

Aplikasi pengelolaan parkir kendaraan berbasis web — mencatat kendaraan masuk dan keluar, menghitung tarif otomatis berdasarkan durasi, serta menyajikan laporan yang dapat diekspor ke PDF, Excel, dan CSV. Dibangun dengan PHP murni (tanpa framework) dan MySQL.

## Dokumentasi

| Halaman | Isi |
|---|---|
| [Daftar Fitur](docs/FITUR.md) | Seluruh fitur dan mikro-fitur (termasuk popup konfirmasi SweetAlert2) |
| [Alur Data Tiap Endpoint](docs/ALUR-DATA.md) | Alur request → validasi → database → respons tiap endpoint, dengan contoh dan data dummy |
| [ERD & Skema Database](docs/ERD.md) | Diagram relasi entitas dan `CREATE TABLE` siap impor |

## Syarat

- PHP 8.x + ekstensi `pdo_mysql`, `mbstring`, `zip`, `gd`/`xml` (untuk export Excel/PDF)
- MySQL/MariaDB (disarankan via Laragon)
- Composer 2.x
- Apache dengan `mod_rewrite` (atau Laragon)

## Cara Instalasi

1. **Clone repositori** ke folder web server (contoh Laragon):
   ```bash
   git clone https://github.com/SiYusup/Web-Aplikasi-Parkir.git
   cd Web-Aplikasi-Parkir
   ```

2. **Instal dependensi PHP:**
   ```bash
   composer install
   ```

3. **Buat database dan tabel.** Buat database `db_parkir` di MySQL, lalu impor skema dari [docs/ERD.md](docs/ERD.md#skema-sql-siap-impor) (tersedia pernyataan `CREATE TABLE` untuk `tb_users` dan `tb_parkir`).

4. **Sesuaikan koneksi bila perlu** di `config/database.php` (bawaan: host `localhost:3306`, user `root`, password kosong, database `db_parkir`).

5. **Arahkan docroot ke folder `public/`** (mis. virtual host Laragon menunjuk ke `.../Web-Aplikasi-Parkir/public`), pastikan `mod_rewrite` aktif, lalu buka:
   ```
   http://localhost:8000/
   ```
   atau sesuai nama virtual host Laragon Anda.

6. **Login.** Buat satu user parkir langsung di database (password memakai `password_hash` PHP), contoh:
   ```sql
   INSERT INTO tb_users (username, password) VALUES ('admin', '$2y$10$HASIL_PASSWORD_HASH_ANDA');
   ```
   Cara membuat hash: `php -r "echo password_hash('admin123', PASSWORD_DEFAULT);"`, lalu ganti nilainya di SQL di atas dan login dengan username `admin`.

## Menjalankan Tes

```bash
vendor/bin/phpunit tests/Config/DatabaseTest.php
```

> Perhatian: `tests/Repository/UserRepositoryTest.php` menjalankan `DELETE FROM tb_users` pada database `db_parkir` asli. Hanya jalankan di database lokal pengembangan.

## Struktur Direktori

```
app/Controller   # HomeController, UserController, ParkirController
app/Service      # UserService, ParkirService (tarif), SessionService (cookie + flash)
app/Repository   # UserRepository, ParkirRepository (query tb_users, tb_parkir)
app/Domain       # User, Parkir (objek data)
app/Model        # DTO request/response
app/Middleware   # MustLoginMiddleware, MustNotLoginMiddleware
app/App          # Router, View
app/View         # Template PHP + SweetAlert2 (footer.php)
config/          # Konfigurasi database
public/          # Front controller (index.php) + .htaccess
tests/           # PHPUnit
docs/            # Dokumentasi lanjutan
```
