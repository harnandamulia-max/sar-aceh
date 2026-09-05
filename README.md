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
   git clone [https://github.com/harnandamulia-max/sar-aceh.git](https://github.com/harnandamulia-max/sar-aceh.git)