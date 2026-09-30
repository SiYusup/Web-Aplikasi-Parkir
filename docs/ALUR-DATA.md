# Alur Data Tiap Endpoint

> Kembali ke [README](../README.md) · Lihat juga [Daftar Fitur](FITUR.md) · [ERD](ERD.md)

Dokumen ini menjelaskan setiap endpoint seperti dokumentasi API: parameter, validasi, query database, dan respons — lengkap dengan **contoh dan data dummy** agar alurnya konkret.

Konvensi umum:
- Router: pencocokan path **persis** (`public/index.php` → `app/App/Router.php`), tanpa parameter URL.
- Respons halaman memakai `View::render(nama_view, $model)` → `header.php + view + footer.php`.
- Redirect memakai `View::redirect(url)` + flash message (`success`/`error`) yang otomatis tampil sebagai popup SweetAlert2.
- Auth dibaca dari cookie `parkir_login = username` (`SessionService::current()`).

Data dummy yang dipakai di seluruh contoh:

| Kunci | Nilai |
|---|---|
| User | username `admin`, password `admin123` (tersimpan sebagai hash `password_hash`) |
| Motor | plat `B 1234 CD`, `jenis_kendaraan = roda_2`, masuk `2026-09-30 08:05:00` |
| Mobil | plat `D 5678 EF`, `jenis_kendaraan = roda_4`, masuk `2026-09-30 09:40:00` |

---

## 1. `POST /login` — Login

**Parameter form:** `username`, `password`.

```
Browser ──POST username=admin&password=admin123──▶ UserController::postLogin
  → UserService::login
      → validasi: keduanya wajib diisi (trim ≠ "")
      → UserRepository::findByUsername("admin")
          → SELECT id, username, password FROM tb_users WHERE username = ?
      → password_verify("admin123", hash_db)  → cocok
  → SessionService::create("admin")   → setcookie parkir_login=admin (1 hari) + $_COOKIE
  → setFlash(success, "Login berhasil. Selamat datang, admin!")
  → redirect /dashboard  ──▶ popup sukses SweetAlert2 ──▶ Dashboard
```

- **Gagal** (user tidak ada / password salah): `ValidationException("Username or password is wrong")` → render ulang `User/login` dengan `error`, tanpa redirect.
- **Contoh dummy gagal:** `username=admin&password=salah` → halaman login menampilkan *"Username or password is wrong"*.

## 2. `GET /logout` — Logout

```
Browser ──klik Logout──▶ [SweetAlert: "Yakin ingin logout?"]
  → Ya ──GET /logout──▶ UserController::logout
      → SessionService::destroy()  → hapus cookie parkir_login
      → redirect /   (halaman utama; Batal = tidak terjadi apa-apa)
```

## 3. `POST /parkir-masuk` — Catat kendaraan masuk

**Parameter form:** `nomor_plat` (contoh `b 1234 cd`), `jenis_kendaraan` (`roda_2`/`roda_4`).

```
Browser ──isi plat B 1234 CD + pilih Motor──▶ [SweetAlert: "Catat kendaraan B 1234 CD masuk parkir?"]
  → Ya ──POST──▶ ParkirController::postMasuk
      → ParkirService::masuk
          → validasi: plat wajib diisi; jenis harus roda_2/roda_4
          → normalisasi: "b 1234 cd" → "B 1234 CD"
          → ParkirRepository::findActiveByNomorPlat("B 1234 CD")
              → SELECT ... WHERE nomor_plat=? AND status='parkir' AND deleted_at IS NULL
          → jika sudah ada: ValidationException("Kendaraan dengan plat B 1234 CD sudah tercatat parkir.")
          → ParkirRepository::add
              → INSERT INTO tb_parkir(nomor_plat, jenis_kendaraan, status, deleted_at)
                VALUES ('B 1234 CD', 'roda_2', 'parkir', NULL)
                -- waktu_masuk terisi otomatis (DEFAULT CURRENT_TIMESTAMP)
      → setFlash(success, "Kendaraan B 1234 CD berhasil tercatat masuk parkir.")
      → redirect /parkir-masuk  ──▶ popup sukses SweetAlert2
```

- **Gagal validasi/duplikat:** render ulang `parkir/masuk` dengan `error` + `old` (isian tidak hilang), tanpa redirect.

## 4. `POST /parkir-keluar` — Keluarkan kendaraan

**Parameter form:** `id` (id baris `tb_parkir`, contoh `7` untuk `B 1234 CD`).

```
Browser ──klik Keluar pada B 1234 CD──▶ [SweetAlert: "Keluarkan kendaraan B 1234 CD?"]
  → Ya ──POST id=7──▶ ParkirController::postKeluar
      → ParkirService::keluar
          → validasi: id > 0
          → ParkirRepository::findById(7)  → status harus 'parkir'
          → hitungBiaya: masuk 08:05 → keluar 10:15 = 130 menit → ceil = 3 jam
              → motor: 2000 + (3-1)×1000 = Rp 4.000
          → ParkirRepository::update
              → UPDATE tb_parkir SET status='selesai', total_bayar='4000',
                waktu_keluar='2026-09-30 10:15:00' WHERE id=7
      → setFlash(success, "Kendaraan B 1234 CD berhasil keluar. Total bayar Rp 4.000.")
      → redirect /parkir-keluar  ──▶ popup sukses SweetAlert2
```

- **Gagal** (id tidak valid / status bukan `parkir`): `setFlash(error, "Kendaraan tidak ditemukan atau sudah keluar.")` → redirect.
- **Contoh dummy mobil:** `D 5678 EF` masuk 09:40, keluar 10:10 (30 menit → 1 jam) → 5000 + 0×1000 = **Rp 5.000**.

## 5. `GET /laporan` — Tabel laporan + filter

**Parameter query (opsional):** `tanggal` (`YYYY-MM-DD`), `cari` (potongan plat).

```
Browser ──GET /laporan?tanggal=2026-09-30&cari=B 1234──▶ ParkirController::laporan
  → ParkirRepository::findAll("2026-09-30", "B 1234")
      → SELECT ... FROM tb_parkir WHERE deleted_at IS NULL
        AND DATE(waktu_masuk) = '2026-09-30'
        AND nomor_plat LIKE '%B 1234%'
        ORDER BY waktu_masuk DESC
  → totalPendapatan = Σ total_bayar where status='selesai'
      → contoh: B 1234 CD (4000, selesai) + D 5678 EF (5000, selesai) = Rp 9.000
  → render parkir/laporan (tabel + badge total + badge filter aktif)
```

Tanpa parameter, kedua filter dilewati dan seluruh data tampil.

## 6. `GET /laporan/pdf`, `/laporan/excel`, `/laporan/csv` — Export

Ketiganya memakai **filter dan query yang sama persis** dengan `GET /laporan`; hanya format keluarannya berbeda. Masing-masing didahului popup konfirmasi SweetAlert2 ("Export laporan ke PDF/Excel/CSV?").

| Endpoint | Respons sukses (contoh dummy filter di atas) |
|---|---|
| `/laporan/pdf?tanggal=2026-09-30&cari=B 1234` | Unduhan `Laporan_Parkir_20260930_101530.pdf` (A4 landscape): judul, "Tanggal 30-09-2026 - Plat: B 1234", 1 baris `B 1234 CD / Motor / 2026-09-30 08:05:00 / 2026-09-30 10:15:00 / Selesai / Rp 4.000`, total Rp 4.000 |
| `/laporan/excel?...` (sama) | Unduhan `Laporan_Parkir_....xlsx`: kolom No, Nomor Plat, Jenis, Waktu Masuk, Waktu Keluar, Status, Total Bayar (Rp, numerik), + baris Total Pendapatan |
| `/laporan/csv?...` (sama) | Unduhan `Laporan_Parkir_....csv`, delimiter `;`, BOM UTF-8, isi baris setara Excel |

- **Data kosong:** PDF berisi baris *"Belum ada data parkir."*; Excel/CSV tetap terunduh berisi header + total 0.
- **Belum login:** ketiga endpoint (seperti semua halaman ber-middleware) redirect ke `/login`.

## 7. `GET /dashboard` — Statistik

```
Browser ──GET /dashboard──▶ ParkirController::dashboard
  → ParkirRepository::stats()  → 4× SELECT COUNT(*) :
      totalHariIni   = masuk hari ini (contoh dummy: 2)
      motorKeluar    = selesai hari ini & roda_2 (contoh: 1)
      mobilKeluar    = selesai hari ini & roda_4 (contoh: 1)
      masihParkir    = status='parkir' (contoh: 0)
  → render Home/dashboard dengan $stats
```
