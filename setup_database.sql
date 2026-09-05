-- ================================================================
-- JALANKAN FILE INI DI phpMyAdmin > tab SQL > database db_sar_aceh
-- Skrip ini aman dijalankan di database KOSONG (instalasi baru)
-- maupun di database yang SUDAH ADA datanya (instalasi lama).
--
-- CREATE TABLE di bawah memakai IF NOT EXISTS, jadi kalau tabelnya
-- sudah ada, baris itu otomatis dilewati. Bagian ALTER TABLE di
-- paling bawah hanya untuk menambal instalasi lama yang tabelnya
-- sudah ada tapi belum punya kolom tertentu.
--
-- Kalau ada baris yang muncul "Duplicate column/table" -- itu
-- artinya sudah pernah dijalankan sebelumnya, aman untuk diabaikan.
-- ================================================================

-- 1. Tabel akun pengguna (login & register)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    failed_attempts INT NOT NULL DEFAULT 0,
    locked_until DATETIME NULL DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- 2. Tabel riwayat pencarian (hasil kalkulator, milik masing-masing user)
--    Sebelumnya file ini cuma berisi ALTER TABLE untuk tabel ini,
--    padahal CREATE TABLE-nya sendiri tidak pernah ada di sini --
--    jadi instalasi baru akan gagal total (tabel tidak pernah
--    terbentuk). Ditambahkan di bawah ini.
CREATE TABLE IF NOT EXISTS riwayat_pencarian (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    lkp_lat DOUBLE NOT NULL,
    lkp_lng DOUBLE NOT NULL,
    base_speed FLOAT NOT NULL DEFAULT 1,
    jumlah_hari FLOAT NOT NULL,
    skala_peta INT NOT NULL,
    tanggal TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_riwayat_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_riwayat_user (user_id),
    INDEX idx_riwayat_tanggal (tanggal)
) ENGINE=InnoDB;

-- ================================================================
-- PENAMBALAN untuk instalasi LAMA yang tabelnya sudah dibuat lebih
-- dulu (sebelum kolom-kolom di bawah ini ada). Di database yang
-- baru dibuat dari kosong, baris-baris ini tidak berpengaruh apa-apa
-- karena kolomnya sudah otomatis ada dari CREATE TABLE di atas
-- (akan muncul error "Duplicate column", aman diabaikan).
-- ================================================================

-- Kolom kecepatan korban, kalau riwayat_pencarian sudah ada tanpa ini
ALTER TABLE riwayat_pencarian
    ADD COLUMN base_speed FLOAT NOT NULL DEFAULT 1 AFTER lkp_lng;

-- Kolom pemilik data, kalau riwayat_pencarian sudah ada tanpa ini
-- (data lama otomatis "dimiliki" user id=1 -- sesuaikan manual kalau perlu)
ALTER TABLE riwayat_pencarian
    ADD COLUMN user_id INT NOT NULL DEFAULT 1 AFTER id;

-- Kolom penguncian akun setelah gagal login berkali-kali,
-- kalau users sudah ada tanpa ini (dipakai oleh login.php)
ALTER TABLE users
    ADD COLUMN failed_attempts INT NOT NULL DEFAULT 0;

ALTER TABLE users
    ADD COLUMN locked_until DATETIME NULL DEFAULT NULL;
