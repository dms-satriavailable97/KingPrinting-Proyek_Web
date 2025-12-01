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

CREATE TABLE desain_produk (
    id INT AUTO_INCREMENT PRIMARY KEY,
    produk VARCHAR(100) NOT NULL,
    gambar VARCHAR(255) NOT NULL,
    caption VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Hapus tabel jika sudah ada (optional)
DROP TABLE IF EXISTS website_items;
DROP TABLE IF EXISTS website_hero_slides;
DROP TABLE IF EXISTS website_colors;
DROP TABLE IF EXISTS website_sections;

-- Tabel untuk sections
CREATE TABLE website_sections (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_name VARCHAR(100) UNIQUE,
    title TEXT,
    subtitle TEXT,
    description TEXT,
    additional_info TEXT, -- Untuk map embed
    meta_data TEXT, -- Untuk footer text
    is_active BOOLEAN DEFAULT true,
    sort_order INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk items (layanan, steps, keunggulan, FAQ)
CREATE TABLE website_items (
    id INT PRIMARY KEY AUTO_INCREMENT,
    section_name VARCHAR(100),
    item_type ENUM('service', 'step', 'feature', 'faq'),
    title VARCHAR(255),
    description TEXT,
    icon VARCHAR(100),
    image_path VARCHAR(255),
    button_text VARCHAR(100),
    link_url VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabel untuk hero slides
CREATE TABLE website_hero_slides (
    id INT PRIMARY KEY AUTO_INCREMENT,
    image_path VARCHAR(255),
    title VARCHAR(255),
    subtitle VARCHAR(255),
    button_text VARCHAR(100),
    button_link VARCHAR(255),
    sort_order INT DEFAULT 0,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


-- Insert default sections
INSERT INTO website_sections (section_name, title, subtitle, description, is_active, sort_order) VALUES 
('hero', 'Raja di Dunia {highlight}Promosi & Advertising{/highlight}', 'Spanduk, banner, stiker, baliho, brosur, dan berbagai kebutuhan cetak lainnya dengan kualitas Terbaik dan harga terjangkau.', '', true, 1),
('services', 'Layanan {highlight}Kami{/highlight}', '', '', true, 2),
('steps', 'Cara Memesan di {highlight}King Printing{/highlight}', '', '', true, 3),
('features', 'Mengapa Memilih {highlight}King Printing{/highlight}?', '', '', true, 4),
('faq', 'Pertanyaan {highlight}Umum{/highlight}', 'Berikut adalah beberapa hal yang sering ditanyakan oleh pelanggan kami. Klik pada pertanyaan untuk melihat jawabannya.', 'Tanya Jawab', true, 5);

-- Insert section contact dengan data lengkap
INSERT INTO website_sections (section_name, title, subtitle, description, additional_info, meta_data, is_active, sort_order) VALUES 
('contact', '+62 887-0584-4251', 'kingdprint@gmail.com', 'Jl. Ahmad Yani 2 No.12 RT.10, Temindung Permai, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur', '<iframe class="map-iframe" src="https://www.google.com/maps/embed?pb=!4v1762266865140!6m8!1m7!1sWhc2abtYaDzUcfT_F7LnHg!2m2!1d-0.4730531793778449!2d117.1653334981797!3f326.50841155499506!4f-15.840080292808594!5f1.0886293032444474" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>', '© 2025 King Printing. All rights reserved.', true, 6);

-- Insert default hero slides
INSERT INTO website_hero_slides (image_path, title, subtitle, button_text, button_link, sort_order, is_active) VALUES 
('assets/heroslide1.jpg', 'Slide 1', '', '', '', 1, true),
('assets/heroslide2.jpg', 'Slide 2', '', '', '', 2, true),
('assets/heroslide3.jpg', 'Slide 3', '', '', '', 3, true);

-- Insert default services
INSERT INTO website_items (section_name, item_type, title, description, icon, button_text, sort_order, is_active) VALUES 
('services', 'service', 'Spanduk & Banner', 'Cetak spanduk dan banner dengan berbagai ukuran dan bahan berkualitas untuk kebutuhan promosi Anda.', 'fas fa-flag', 'Pesan Sekarang', 1, true),
('services', 'service', 'Stiker & Label', 'Stiker berkualitas untuk produk, kemasan, atau promosi dengan berbagai pilihan bahan ternama dan finishing.', 'fas fa-sticky-note', 'Pesan Sekarang', 2, true),
('services', 'service', 'Baliho & Billboard', 'Solusi cetak baliho ukuran besar untuk iklan luar ruangan dengan ketajaman gambar maksimal dan bahan berkualitas.', 'fas fa-bullhorn', 'Pesan Sekarang', 3, true),
('services', 'service', 'Brosur & Flyer', 'Brosur dan flyer dengan desain menarik untuk promosi bisnis, acara, atau produk Anda.', 'fas fa-newspaper', 'Pesan Sekarang', 4, true),
('services', 'service', 'Kemasan & Dus', 'Kemasan produk dan dus custom dengan desain menarik untuk meningkatkan nilai produk Anda.', 'fas fa-box', 'Pesan Sekarang', 5, true),
('services', 'service', 'Produk Custom', 'Kami juga menerima pesanan produk cetak custom sesuai kebutuhan khusus Anda.', 'fas fa-palette', 'Pesan Sekarang', 6, true);

-- Insert default steps
INSERT INTO website_items (section_name, item_type, title, description, icon, sort_order, is_active) VALUES 
('steps', 'step', 'Pilih Produk', 'Pilih produk dan klik tombol "Pesan Sekarang" untuk membuka form pemesanan.', 'fas fa-mouse-pointer', 1, true),
('steps', 'step', 'Isi Form', 'Lengkapi detail pesanan Anda pada form yang muncul, seperti ukuran, bahan, dan jumlah.', 'fas fa-ruler-combined', 2, true),
('steps', 'step', 'Kirim ke WhatsApp', 'Klik tombol kirim dan data pesanan Anda akan kami terima di sistem dan juga WhatsApp.', 'fab fa-whatsapp', 3, true),
('steps', 'step', 'Produk Siap', 'Setelah pesanan selesai, kami akan menghubungi Anda untuk proses pengambilan di toko.', 'fas fa-shipping-fast', 4, true);

-- Insert default features
INSERT INTO website_items (section_name, item_type, title, description, icon, sort_order, is_active) VALUES 
('features', 'feature', 'Kualitas Terbaik', 'Hasil cetak dengan kualitas terbaik.', 'fas fa-crown', 1, true),
('features', 'feature', 'Proses Cepat', 'Pengerjaan cepat tanpa mengorbankan kualitas.', 'fas fa-bolt', 2, true),
('features', 'feature', 'Harga Terjangkau', 'Harga kompetitif dengan kualitas yang tidak mengecewakan.', 'fas fa-tags', 3, true),
('features', 'feature', 'Layanan Ramah', 'Tim customer service yang siap membantu Anda.', 'fas fa-headset', 4, true);

-- Insert default FAQs
INSERT INTO website_items (section_name, item_type, title, description, sort_order, is_active) VALUES 
('faq', 'faq', 'Berapa lama proses pengerjaan pesanan?', 'Waktu pengerjaan tergantung pada jenis produk dan jumlah pesanan. Untuk spanduk/banner standar, proses biasanya memakan waktu 20–30 menit. Untuk pesanan custom atau dalam jumlah besar, estimasi waktu pengerjaan akan kami informasikan di awal.', 1, true),
('faq', 'faq', 'Apakah bisa bantu buatkan desainnya?', 'Tentu saja! Kami dapat membantu dalam membuat desainnya. Silakan lampirkan detail, teks, atau sketsa kasar ide Anda pada kolom catatan saat memesan.', 2, true),
('faq', 'faq', 'Apakah ada minimal order?', 'Sebagian besar produk (spanduk, banner) bisa dipesan satuan. Namun untuk produk kecil seperti stiker label atau kemasan dus, mungkin ada minimum order untuk efisiensi harga.', 3, true),
('faq', 'faq', 'Bagaimana sistem pembayarannya?', 'Pembayaran dilakukan melalui transfer bank (BCA) atau bayar langsung di tempat.', 4, true),
('faq', 'faq', 'Bagaimana cara mengambil pesanan yang sudah selesai?', 'Pesanan yang telah selesai akan kami konfirmasi kepada pelanggan untuk segera diambil di toko.', 5, true);

-- Tambah table baru untuk jam operasional
CREATE TABLE IF NOT EXISTS operational_hours (
    id INT PRIMARY KEY AUTO_INCREMENT,
    day_type VARCHAR(50) UNIQUE,
    hours_text VARCHAR(100),
    is_open BOOLEAN DEFAULT true,
    sort_order INT DEFAULT 0
);

-- Insert default jam operasional
INSERT INTO operational_hours (day_type, hours_text, is_open, sort_order) VALUES 
('weekdays', 'Senin - Sabtu: 09.00 - 18.00', true, 1),
('sunday', 'Minggu: Tutup', false, 3);

CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Password default: admin
-- Hash di bawah ini adalah hash dari kata 'admin'
INSERT INTO admins (username, password) VALUES 
('admin', '$2y$10$XN/sHjA/gA.q.y/y.q.y.u1y.y.y.y.y.y.y.y.y.y.y.y.y.y');