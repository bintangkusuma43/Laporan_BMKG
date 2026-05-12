CREATE DATABASE IF NOT EXISTS laporan_bmkg CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE laporan_bmkg;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'petugas') NOT NULL DEFAULT 'petugas',
    petugas_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS petugas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama VARCHAR(100) NOT NULL,
    nip VARCHAR(30) NULL,
    upt VARCHAR(150) NOT NULL,
    jabatan VARCHAR(100) NULL,
    kontak VARCHAR(50) NULL,
    email VARCHAR(100) NULL,
    UNIQUE KEY uniq_petugas_nip (nip),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS stasiun (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode VARCHAR(20) NOT NULL UNIQUE,
    nama VARCHAR(150) NOT NULL,
    lokasi VARCHAR(255) NOT NULL,
    tipe ENUM('WRS', 'Accelerograph', 'Intensitymeter', 'Seismograph', 'Lainnya') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

ALTER TABLE users
    ADD CONSTRAINT fk_users_petugas
        FOREIGN KEY (petugas_id) REFERENCES petugas(id)
        ON DELETE SET NULL ON UPDATE CASCADE;

CREATE TABLE IF NOT EXISTS laporan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    kode_laporan VARCHAR(30) NOT NULL UNIQUE,
    jenis ENUM('wrs_ng', 'accelerograph', 'seismograph') NOT NULL,
    user_id INT NOT NULL,
    petugas_id INT NULL,
    stasiun_id INT NULL,
    nomor_surat VARCHAR(100) NULL,
    tanggal_laporan DATE NOT NULL,
    status ENUM('draft', 'diajukan', 'diproses', 'selesai', 'ditolak') NOT NULL DEFAULT 'draft',
    submitted_at TIMESTAMP NULL DEFAULT NULL,
    catatan_admin TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_laporan_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_laporan_petugas FOREIGN KEY (petugas_id) REFERENCES petugas(id) ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_laporan_stasiun FOREIGN KEY (stasiun_id) REFERENCES stasiun(id) ON DELETE SET NULL ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS laporan_detail (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    detail_type ENUM('wrs_ng', 'accelerograph', 'seismograph') NOT NULL,
    detail_json JSON NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_detail_laporan FOREIGN KEY (laporan_id) REFERENCES laporan(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS dokumentasi_foto (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    caption VARCHAR(255) NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_foto_laporan FOREIGN KEY (laporan_id) REFERENCES laporan(id) ON DELETE CASCADE ON UPDATE CASCADE
);

CREATE TABLE IF NOT EXISTS laporan_lampiran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    laporan_id INT NOT NULL,
    lampiran_type ENUM('surat_tugas', 'checklist') NOT NULL,
    file_name VARCHAR(255) NOT NULL,
    uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_lampiran_laporan FOREIGN KEY (laporan_id) REFERENCES laporan(id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY uniq_lampiran (laporan_id, lampiran_type)
);

CREATE INDEX idx_laporan_jenis ON laporan (jenis);
CREATE INDEX idx_laporan_status ON laporan (status);
CREATE INDEX idx_detail_type ON laporan_detail (detail_type);
