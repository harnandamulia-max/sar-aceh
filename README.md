# Kalkulator Area Pencarian SAR — BASARNAS Aceh

Aplikasi web untuk menghitung area pencarian dalam operasi SAR (*Search and Rescue*), dibuat untuk Kantor Pencarian dan Pertolongan (BASARNAS) Aceh. Aplikasi menghitung *Possible Search Area* dan *Probable Search Area* berdasarkan kecepatan pergerakan korban dan lama waktu hilang, lalu menampilkan hasilnya di atas peta interaktif.

## Fitur

- Login & registrasi akun (autentikasi berbasis sesi PHP)
- Kalkulator area pencarian (Possible & Probable Search Area, radius di peta sesuai skala)
- Visualisasi hasil di peta interaktif (Leaflet + OpenStreetMap)
- Riwayat pencarian tersimpan, dengan filter periode (minggu/bulan/tahun/custom) dan opsi cetak
- Desain responsif bertema maritim

## Tech Stack

- PHP (native, mysqli)
- MySQL / MariaDB
- Leaflet.js + OpenStreetMap (peta)
- Vanilla CSS/JS

## Instalasi

1. Clone repo ini ke folder yang dilayani server PHP Anda (misalnya `htdocs` di XAMPP):
   ```bash
   git clone https://github.com/USERNAME/NAMA-REPO.git
   ```

2. Buat database baru (misalnya `db_sar_aceh`), lalu import `setup_database.sql` lewat phpMyAdmin (tab SQL) atau CLI:
   ```bash
   mysql -u root -p db_sar_aceh < setup_database.sql
   ```

3. Salin `config.example.php` menjadi `config.php`, lalu sesuaikan kredensial database:
   ```bash
   cp config.example.php config.php
   ```

4. Jalankan server, misalnya dengan PHP built-in server:
   ```bash
   php -S localhost:8000
   ```
   Lalu buka `http://localhost:8000` di browser.

5. Daftar akun baru lewat halaman **Register**, lalu login untuk mengakses kalkulator.

## Struktur File

| File | Keterangan |
|---|---|
| `index.php` | Form input kalkulator |
| `proses_simpan.php` | Proses hitung & simpan ke database |
| `hasil.php` | Halaman hasil perhitungan + peta |
| `riwayat.php` | Daftar riwayat pencarian |
| `functions.php` | Rumus perhitungan SAR |
| `login.php`, `register.php`, `logout.php`, `auth_check.php` | Sistem autentikasi |
| `header.php`, `nav.php`, `auth_visual.php` | Komponen tampilan |
| `config.example.php` | Contoh konfigurasi koneksi database |
| `setup_database.sql` | Skema database |

## Rumus Perhitungan

Berdasarkan materi "Menentukan Area Pencarian":

1. **Possible Search Area** — r1 = kecepatan korban (km/hari) × jumlah hari hilang; Luas1 = π × r1²
2. **Probable Search Area** — hasil analisa/survey = 1/8 dari Possible Search Area; Luas2 = Luas1 × 1/8 (r2 = r1/√8)
3. **Luas Area Pencarian Akhir** — bentuk persegi yang mengelilingi Probable Search Area; sisi = 2 × r2

## Catatan

`config.php` berisi kredensial database dan **tidak** ikut ter-commit ke Git (lihat `.gitignore`). Gunakan `config.example.php` sebagai referensi untuk membuat `config.php` milik Anda sendiri.
