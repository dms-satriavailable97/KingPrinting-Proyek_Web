CREATE DATABASE king_printing_db;

USE king_printing_db;

CREATE TABLE pesanan (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nama_pemesan VARCHAR(255) NOT NULL,
    telepon VARCHAR(20) NOT NULL,
    produk VARCHAR(100) NOT NULL,
    ukuran VARCHAR(50),
    bahan VARCHAR(50),
    jumlah INT NOT NULL,
    catatan TEXT,
    tanggal_masuk TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status VARCHAR(20) DEFAULT 'Tertunda'
);