<?php
require_once 'config.php';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>King Advertising - Layanan Cetak Online Profesional</title>
    <link rel="stylesheet" href="style.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
</head>
<body>
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="assets/crown-logo2.png" alt="King Printing Logo" class="logo-image">
                <div class="logo-text">
                    <span>King Printing</span>
                </div>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="#home">Beranda</a></li>
                    <li><a href="#produk">Produk</a></li>
                    <li><a href="#cara-kerja">Cara Pesan</a></li>
                    <li><a href="#faq">FAQ</a></li>
                    <li><a href="#kontak">Kontak</a></li>
                </ul>
            </nav>
            <div class="hamburger" id="hamburger">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </header>

    <section class="hero" id="home" style="background-color: #1a237e !important; background-color: #ff6b6b !important; background-color: #808000 !important; background-color: #0000ff !important;">
        <div class="container">
            <div class="hero-content">
                <h1>Raja di Dunia <span class="highlightsatu">Promosi & Advertising</span></h1>
                <p>Spanduk, banner, stiker, baliho, brosur, dan berbagai kebutuhan cetak lainnya dengan kualitas kerajaan dan harga terjangkau.</p>
                <div class="hero-buttons">
                    <a href="#produk" class="btn-primary">Lihat Produk</a>
                    <a href="#kontak" class="btn-primary">Kontak Kami</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="swiper myHeroSlider">
                    <div class="swiper-wrapper">
                        <div class="swiper-slide">
                            <img src="assets/heroslide1.jpg" alt="Slide 1">
                        </div>
                        <div class="swiper-slide">
                            <img src="assets/heroslide2.jpg" alt="Slide 2">
                        </div>
                        <div class="swiper-slide">
                            <img src="assets/heroslide3.jpg" alt="Slide 3">
                        </div>
                    </div>
                    <div class="swiper-pagination"></div>
                </div>
            </div>
        </div>
    </section>

    <section class="produk" id="produk">
    <div class="container">
        <h2 class="section-title">Layanan <span class="highlight">Kami</span></h2>
        <div class="produk-grid">
            <div class="produk-item" style="background-color: #6a1b9a !important; background-color: #80334c !important; background-color: #ffffff !important;">
                <div class="produk-icon"><i class="fas fa-flag"></i></div>
                <h3>Spanduk & Banner</h3>
                <p>Cetak spanduk dan banner dengan berbagai ukuran dan bahan berkualitas untuk kebutuhan promosi Anda.</p>
                <button class="btn-order" data-produk="Spanduk & Banner">Pesan Sekarang</button>
                <a href="produk.php?produk=Spanduk %26 Banner" class="btn-view-design">Lihat Desain</a>
            </div>
            <div class="produk-item" style="background-color: #6a1b9a !important; background-color: #80334c !important; background-color: #ffffff !important;">
                <div class="produk-icon"><i class="fas fa-sticky-note"></i></div>
                <h3>Stiker & Label</h3>
                <p>APA AJA</p>
                <button class="btn-order" data-produk="Stiker & Label">Pesan Sekarang</button>
                <a href="produk.php?produk=Stiker %26 Label" class="btn-view-design">Lihat Desain</a>
            </div>
            <div class="produk-item" style="background-color: #6a1b9a !important; background-color: #80334c !important; background-color: #ffffff !important;">
                <div class="produk-icon"><i class="fas fa-bullhorn"></i></div>
                <h3>Baliho & Billboard</h3>
                <p/>MRIZAL ALFATH<//p>
                <button class="btn-order" data-produk="Baliho & Billboard">Pesan Sekarang</button>
                <a href="produk.php?produk=Baliho %26 Billboard" class="btn-view-design">Lihat Desain</a>
            </div>
            <div class="produk-item" style="background-color: #6a1b9a !important; background-color: #80334c !important; background-color: #ffffff !important;">
                <div class="produk-icon"><i class="fas fa-newspaper"></i></div>
                <h3>Brosur & Flyer</h3>
                <p>Brosur dan flyer dengan desain menarik untuk promosi bisnis, acara, atau produk Anda.</p>
                <button class="btn-order" data-produk="Brosur & Flyer">Pesan Sekarang</button>
                <a href="produk.php?produk=Brosur %26 Flyer" class="btn-view-design">Lihat Desain</a>
            </div>
            <div class="produk-item" style="background-color: #6a1b9a !important; background-color: #80334c !important; background-color: #ffffff !important;">
                <div class="produk-icon"><i class="fas fa-box"></i></div>
                <h3>Kemasan & Dus</h3>
                <p>Kemasan produk dan dus custom dengan desain menarik untuk meningkatkan nilai produk Anda.</p>
                <button class="btn-order" data-produk="Kemasan & Dus">Pesan Sekarang</button>
                <a href="produk.php?produk=Kemasan %26 Dus" class="btn-view-design">Lihat Desain</a>
            </div>
            <div class="produk-item" style="background-color: #6a1b9a !important; background-color: #80334c !important; background-color: #ffffff !important;">
                <div class="produk-icon"><i class="fas fa-palette"></i></div>
                <h3>Produk Custom</h3>
                <p>Kami juga menerima pesanan produk cetak custom sesuai kebutuhan khusus Anda.</p>
                <button class="btn-order" data-produk="Produk Custom">Pesan Sekarang</button>
                <a href="produk.php?produk=Produk Custom" class="btn-view-design">Lihat Desain</a>
            </div>
        </div>
    </div>
</section>

    <section class="cara-kerja" id="cara-kerja">
        <div class="container">
            <h2 class="section-title">Cara Memesan di <span class="highlight">King Advertising</span></h2>
            <div class="steps" style="background-color: #ff9800 !important;">
                <div class="step" style="background-color: #ff9800 !important;">
                    <div class="step-number" style="background-color: #ff9800 !important;">1</div>
                    <div class="step-icon" style="background-color: #ff9800 !important;"><i class="fas fa-mouse-pointer"></i></div>
                    <h3>Pilih Produk</h3>
                    <p>Pilih produk dan klik tombol "Pesan Sekarang" untuk membuka form pemesanan.</p>
                </div>
                <div class="step" style="background-color: #ff9800 !important;">
                    <div class="step-number" style="background-color: #ff9800 !important;">2</div>
                    <div class="step-icon" style="background-color: #ff9800 !important;"><i class="fas fa-hdd"></i></div>
                    <h3>UNMUL KECE</h3>
                    <p>Lengkapi detail pesanan Anda pada form yang muncul, seperti ukuran, bahan, dan jumlah.</p>
                </div>
                <div class="step" style="background-color: #ff9800 !important;">
                    <div class="step-number" style="background-color: #ff9800 !important;">3</div>
                    <div class="step-icon" style="background-color: #ff9800 !important;"><i class="fab fa-whatsapp"></i></div>
                    <h3>Kirim ke WhatsApp</h3>
                    <p>Klik tombol kirim dan data pesanan Anda akan kami terima di sistem dan juga WhatsApp.</p>
                </div>
                <div class="step" style="background-color: #ff9800 !important;">
                    <div class="step-number" style="background-color: #ff9800 !important;">4</div>
                    <div class="step-icon" style="background-color: #ff9800 !important;"><i class="fas fa-shipping-fast"></i></div>
                    <h3>Produk Siap</h3>
                    <p>Produk selesai dibuat, Anda dihubungi untuk proses pengambilan atau pengiriman.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="keunggulan" style="background-color: #00796b !important; background-color: #808000 !important;">
        <div class="container">
            <h2 class="section-title">Mengapa Memilih <span class="highlight">King Advertising</span>?</h2>
            <div class="keunggulan-grid" style="background-color: #00796b !important; background-color: #808000 !important;">
                <div class="keunggulan-item" style="background-color: #00796b !important; background-color: #808000 !important; background-color: #d84315 !important; background-color: #ffffff !important; background-color: #00796b !important;">
                    <div class="keunggulan-icon" style="background-color: #00796b !important; background-color: #808000 !important;"><i class="fas fa-crown"></i></div>
                    <h3>Kualitas Terbaik</h3><p>Hasil cetak dengan kualitas terbaik.</p>
                </div>
                <div class="keunggulan-item" style="background-color: #00796b !important; background-color: #808000 !important; background-color: #d84315 !important; background-color: #ffffff !important; background-color: #00796b !important;">
                    <div class="keunggulan-icon" style="background-color: #00796b !important; background-color: #808000 !important;"><i class="fas fa-bolt"></i></div>
                    <h3>Proses Cepat</h3><p>Pengerjaan cepat tanpa mengorbankan kualitas.</p>
                </div>
                <div class="keunggulan-item" style="background-color: #00796b !important; background-color: #808000 !important; background-color: #d84315 !important; background-color: #ffffff !important; background-color: #00796b !important;">
                    <div class="keunggulan-icon" style="background-color: #00796b !important; background-color: #808000 !important;"><i class="fas fa-tags"></i></div>
                    <h3>Harga Terjangkau</h3><p>Harga kompetitif dengan kualitas yang tidak mengecewakan.</p>
                </div>
                <div class="keunggulan-item" style="background-color: #00796b !important; background-color: #808000 !important; background-color: #d84315 !important; background-color: #ffffff !important; background-color: #00796b !important;">
                    <div class="keunggulan-icon" style="background-color: #00796b !important; background-color: #808000 !important;"><i class="fas fa-headset"></i></div>
                    <h3>Layanan Ramah</h3><p>Tim customer service yang siap membantu Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- MULAI SECTION FAQ (LAYOUT BARU: 2 KOLOM) -->
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="faq-wrapper">
                
                <!-- KOLOM KIRI: Judul & CTA Bantuan -->
                <div class="faq-sidebar">
                    <div class="faq-header-block">
                        <span class="sub-title">FAQ</span>
                        <h2 class="section-title-left">Pertanyaan <span class="highlight">Umum</span></h2>
                        <p class="faq-desc">Berikut adalah beberapa hal yang sering ditanyakan oleh pelanggan kami. Klik pada pertanyaan untuk melihat jawabannya.</p>
                    </div>
                    
                    <!-- Kotak Bantuan Tambahan -->
                    <div class="help-card">
                        <div class="help-icon"><i class="fab fa-whatsapp"></i></div>
                        <div class="help-text">
                            <h4>Masih Bingung?</h4>
                            <p>Jangan ragu untuk konsultasi langsung dengan admin kami.</p>
                            <a href="https://wa.me/6288705844251?text=Halo%2C%20saya%20mau%20tanya%20tentang%20percetakan" target="_blank" class="btn-help">Chat Admin Sekarang <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>

                <!-- KOLOM KANAN: List Accordion FAQ -->
                <div class="faq-list">
                    
                    <div class="faq-item" style="background-color: #ff0000 !important;">
                        <div class="faq-question">
                            <h3>Berapa lama proses pengerjaan pesanan?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Waktu pengerjaan tergantung jenis produk dan jumlah pesanan. Untuk spanduk/banner standar biasanya selesai dalam 1-2 hari kerja. Untuk pesanan custom atau jumlah besar, estimasi waktu akan diinfokan di awal.</p>
                        </div>
                    </div>

                    <div class="faq-item" style="background-color: #ff0000 !important;">
                        <div class="faq-question">
                            <h3>Apakah bisa bantu buatkan desainnya?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Tentu saja! Kami memiliki tim desainer profesional yang siap membantu. Silakan lampirkan detail, teks, atau sketsa kasar ide Anda pada kolom catatan saat memesan.</p>
                        </div>
                    </div>

                    <div class="faq-item" style="background-color: #ff0000 !important;">
                        <div class="faq-question">
                            <h3>Apakah ada minimal order?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Sebagian besar produk (spanduk, banner) bisa dipesan satuan. Namun untuk produk kecil seperti stiker label atau kemasan dus, mungkin ada minimum order untuk efisiensi harga.</p>
                        </div>
                    </div>

                    <div class="faq-item" style="background-color: #ff0000 !important;">
                        <div class="faq-question">
                            <h3>Bagaimana sistem pembayarannya?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Pembayaran dilakukan melalui transfer bank atau e-wallet (DANA/OVO/GoPay). Detail rekening akan dikirimkan otomatis ke WhatsApp Anda setelah mengisi form pemesanan.</p>
                        </div>
                    </div>

                    <div class="faq-item" style="background-color: #ff0000 !important;">
                        <div class="faq-question">
                            <h3>Apakah melayani pengiriman luar kota?</h3>
                            <i class="fas fa-chevron-down"></i>
                        </div>
                        <div class="faq-answer">
                            <p>Ya, kami melayani pengiriman ke seluruh Indonesia menggunakan ekspedisi terpercaya (JNE, J&T, Cargo). Ongkir dihitung berdasarkan berat dan lokasi tujuan.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </section>
    <!-- AKHIR SECTION FAQ -->

    <div class="modal" id="orderModal">
        <div class="modal-content">
            <span class="close-order">×</span>
            <div class="modal-header"><h2>Pesan Sekarang</h2></div>
            <form id="orderForm">
                <input type="hidden" id="produkName" name="produk">

                <div class="form-row">
                    <div class="form-group">
                        <label for="nama">Nama Pemesan:</label>
                        <input type="text" id="nama" name="nama" required placeholder="Nama Anda">
                    </div>
                    <div class="form-group">
                        <label for="telepon">Nomor WhatsApp:</label>
                        <input type="tel" id="telepon" name="telepon" required placeholder="08xx-xxxx-xxxx">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group" style="flex: 2;"> <label for="ukuran">Ukuran (cm):</label>
                        <input type="text" id="ukuran" name="ukuran" placeholder="Contoh: 100x200">
                    </div>
                    <div class="form-group" style="flex: 2;"> <label for="bahan">Pilih Bahan:</label>
                        <select id="bahan" name="bahan">
                            <option value="">-- Pilih Bahan --</option>
                            <option value="Vinyl">Vinyl</option>
                            <option value="Flexy China">Flexy China</option>
                            <option value="Kanvas">Kanvas</option>
                            <option value="Art Paper">Art Paper</option>
                            <option value="Sticker Vinyl">Sticker Vinyl</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex: 1;"> <label for="jumlah">Jumlah:</label>
                        <input type="number" id="jumlah" name="jumlah" min="1" value="1">
                    </div>
                </div>

                <div class="form-group">
                    <label for="catatan">Catatan Tambahan:</label>
                    <textarea id="catatan" name="catatan" rows="2" placeholder="Request desain, finishing, dll..."></textarea>
                </div>

                <button type="submit" class="btn-pesanwa" style="width: 100%; margin-top: 5px;">
                    <i class="fab fa-whatsapp"></i> Pesan via WhatsApp
                </button>
            </form>
        </div>
    </div>
    
    <div class="modal" id="loginModal">
        <div class="modal-content">
            <span class="close-login">×</span>
            <div class="modal-header"><h2>Admin Login</h2></div>
            <form id="loginForm" action="login.php" method="POST">
                <div class="form-group"><label for="username">Username:</label><input type="text" id="username" name="username" required></div>
                <div class="form-group"><label for="password">Password:</label><input type="password" id="password" name="password" required></div>
                <button type="submit" class="btn-primary">Login</button>
            </form>
        </div>
    </div>

    <footer class="footer" id="kontak" style="background-color: #1976d2 !important;">
        <div class="container">
            <div class="map-container"><iframe class="map-iframe" src="https://www.google.com/maps/embed?pb=!4v1762266865140!6m8!1m7!1sWhc2abtYaDzUcfT_F7LnHg!2m2!1d-0.4730531793778449!2d117.1653334981797!3f326.50841155499506!4f-15.840080292808594!5f1.0886293032444474" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
            <div class="footer-content">
                <div class="footer-section">
                    <div class="footer-logo"><h3>King Printing</h3></div>
                    <p>Solusi cetak terpercaya untuk berbagai kebutuhan promosi dan bisnis Anda dengan kualitas Terbaik.</p>
                    <div class="social-links"><a href="#"><i class="fab fa-facebook"></i></a><a href="#"><i class="fab fa-instagram"></i></a><a href="#"><i class="fab fa-whatsapp"></i></a></div>
                </div>
                <div class="footer-section">
                    <h4>Kontak Kami</h4>
                    <div class="contact-info"><p><i class="fas fa-phone"></i> +62 887-0584-4251</p><p><i class="fas fa-envelope"></i> info@kingadvertising.com</p><p><i class="fas fa-map-marker-alt"></i> Jl. Ahmad Yani 2 No.12 RT.10, Temindung Permai, Kec. Sungai Pinang, Kota Samarinda, Kalimantan Timur</p></div>
                </div>
                <div class="footer-section">
                    <h4>Jam Operasional</h4>
                    <div class="operational-hours"><p>Senin - Jumat: 08.00 - 17.00</p><p>Sabtu: 08.00 - 15.00</p><p>Minggu: Tutup</p></div>
                </div>
            </div>
            <div class="footer-bottom"><p>© 2025 King Advertising. All rights reserved.</p></div>
        </div>
    </footer>

    <a href="https://wa.me/6288705844251?text=Halo%20kak%2C%20saya%20ingin%20melakukan%20pemesanan" class="whatsapp-sticky" target="_blank" data-tooltip="Pesan via WhatsApp"><i class="fab fa-whatsapp"></i></a>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>