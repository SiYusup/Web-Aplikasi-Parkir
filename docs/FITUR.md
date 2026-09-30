# Daftar Fitur

> Kembali ke [README](../README.md) · Lihat juga [Alur Data](ALUR-DATA.md) · [ERD](ERD.md)

## 1. Autentikasi

| Fitur | Keterangan |
|---|---|
| Login | Username + password, diverifikasi dengan `password_verify`. Gagal login menampilkan pesan `Username or password is wrong` tanpa memberi tahu bagian mana yang salah |
| Logout | Menghapus cookie login, kembali ke halaman utama. **Menggunakan popup konfirmasi SweetAlert2** ("Yakin ingin logout?") |
| Penjagaan halaman | Seluruh halaman kecuali `/` dan `/login` wajib login (`MustLoginMiddleware`); halaman `/login` tidak bisa dibuka saat sudah login (`MustNotLoginMiddleware`) |
| Sesi | Tanpa tabel sesi — login disimpan di cookie `parkir_login = username`, kedaluwarsa 1 hari, path `/` |

## 2. Dashboard (`/dashboard`)

- Kartu statistik hari ini: total kendaraan masuk hari ini, motor keluar hari ini, mobil keluar hari ini, kendaraan yang masih parkir
- Pintasan navigasi ke Parkir Masuk, Parkir Keluar, dan Laporan

## 3. Parkir Masuk (`/parkir-masuk`)

- Form nomor plat (otomatis dinormalisasi ke huruf besar, mis. `b 1234 cd` → `B 1234 CD`) dan pilihan jenis kendaraan (Roda 2 / Roda 4)
- Validasi: plat wajib diisi, jenis wajib `roda_2` atau `roda_4`, plat yang **masih berstatus parkir tidak bisa dicatat dua kali**
- Info tarif ditampilkan di bawah form
- **Mikro-fitur SweetAlert2:**
  - Popup konfirmasi sebelum submit: *"Catat kendaraan B 1234 CD masuk parkir?"* (nama plat terisi otomatis dari input)
  - Popup sukses otomatis setelah redirect: *"Kendaraan B 1234 CD berhasil tercatat masuk parkir."*
  - Popup error otomatis bila validasi gagal di sisi server
- Data lama (`old`) dipertahankan di form saat validasi gagal

## 4. Parkir Keluar (`/parkir-keluar`)

- Daftar seluruh kendaraan yang sedang parkir + kolom pencarian plat
- Tombol **Keluar** per kendaraan; biaya dihitung otomatis dari `waktu_masuk` sampai saat ini:
  - Motor (`roda_2`): Rp 2.000 jam pertama + Rp 1.000 per jam berikutnya
  - Mobil (`roda_4`): Rp 5.000 jam pertama + Rp 1.000 per jam berikutnya
  - Durasi dihitung per menit (detik dibulatkan ke atas 1 menit), lalu dibulatkan ke atas per jam, **minimal 1 jam**
  - Contoh: motor parkir 2 jam 10 menit → Rp 2.000 + 2 × Rp 1.000 = Rp 4.000
- **Mikro-fitur SweetAlert2:**
  - Popup konfirmasi per kendaraan: *"Keluarkan kendaraan B 1234 CD?"* — Batal membatalkan, Ya memproses
  - Popup sukses otomatis: *"Kendaraan B 1234 CD berhasil keluar. Total bayar Rp 4.000."*
- Kendaraan yang sudah keluar tidak bisa dikeluarkan ulang (pesan: *"Kendaraan tidak ditemukan atau sudah keluar."*)

## 5. Laporan (`/laporan`)

- Tabel seluruh riwayat parkir: nomor plat, jenis, waktu masuk/keluar, status (Parkir/Selesai), total bayar
- Filter tanggal (`DATE(waktu_masuk)`) dan pencarian plat (`LIKE %...%`), dapat dikombinasikan; tombol Reset mengosongkan filter
- Badge total pendapatan (akumulasi `total_bayar` berstatus `selesai`) serta badge filter aktif

### 5.1 Export PDF (`/laporan/pdf`)

- Dokumen A4 landscape via Dompdf: judul, label filter, waktu cetak, tabel, total pendapatan
- Bila data kosong, PDF tetap terunduh berisi baris *"Belum ada data parkir."*
- **Popup konfirmasi SweetAlert2** sebelum mengunduh: *"Export laporan ke PDF?"*

### 5.2 Export Excel (`/laporan/excel`)

- File `.xlsx` asli via PhpSpreadsheet: judul + label filter, header gelap tebal, kolom auto-size, kolom Total Bayar numerik berformat angka, baris Total Pendapatan
- Nama file `Laporan_Parkir_YYYYMMDD_HHMMSS.xlsx`
- Menghormati filter tanggal/plat yang sedang tampil
- **Popup konfirmasi SweetAlert2** sebelum mengunduh: *"Export laporan ke Excel?"*

### 5.3 Export CSV (`/laporan/csv`)

- Delimiter `;` + BOM UTF-8 agar terbuka rapi di Excel Indonesia, kolom sama dengan Excel plus baris Total Pendapatan
- Nama file `Laporan_Parkir_YYYYMMDD_HHMMSS.csv`
- Menghormati filter tanggal/plat yang sedang tampil
- **Popup konfirmasi SweetAlert2** sebelum mengunduh: *"Export laporan ke CSV?"*

## 6. Sistem Konfirmasi SweetAlert2 (mekanisme global)

- Library dimuat sekali di `app/View/footer.php` (CDN SweetAlert2 v11)
- Cara pakai untuk pengembang: cukup tambah atribut **`data-confirm="pesan?"`** pada `<form>` atau `<a>` mana pun — submit/navigasi ditahan sampai pengguna menekan *"Ya, lanjutkan"*
- Placeholder khusus **`{plat}`** pada form parkir-masuk otomatis diganti isi input `nomor_plat` (huruf besar)
- Flash message (`success`/`error` dari `SessionService`) otomatis tampil sebagai popup (3,5 detik) selain alert Bootstrap — tanpa mengubah alur `setFlash()` + `redirect` yang ada
