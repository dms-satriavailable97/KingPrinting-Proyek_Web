<?php
require_once 'config.php';

// Ambil data dari database
$hero_section = $conn->query("SELECT * FROM website_sections WHERE section_name = 'hero'")->fetch_assoc();
$services = $conn->query("SELECT * FROM website_items WHERE section_name = 'services' AND item_type = 'service' AND is_active = 1 ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$steps = $conn->query("SELECT * FROM website_items WHERE section_name = 'steps' AND item_type = 'step' AND is_active = 1 ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$features = $conn->query("SELECT * FROM website_items WHERE section_name = 'features' AND item_type = 'feature' AND is_active = 1 ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);
$faqs = $conn->query("SELECT * FROM website_items WHERE section_name = 'faq' AND item_type = 'faq' AND is_active = 1 ORDER BY sort_order")->fetch_all(MYSQLI_ASSOC);

// Process hero title untuk highlight
$hero_title = $hero_section['title'] ?? 'Raja di Dunia {highlight}Promosi & Advertising{/highlight}';
$hero_parts = preg_split('/({highlight}|{\/highlight})/', $hero_title, -1, PREG_SPLIT_DELIM_CAPTURE);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>King Printing - Layanan Cetak Profesional</title>
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

    <section class="hero" id="home">
        <div class="container">
            <div class="hero-content">
                <h1>
                    <?php 
                    $is_highlight = false;
                    foreach($hero_parts as $part): 
                        if ($part === '{highlight}') {
                            $is_highlight = true;
                            continue;
                        } elseif ($part === '{/highlight}') {
                            $is_highlight = false;
                            continue;
                        }
                        
                        if ($is_highlight) {
                            echo '<span class="highlightsatu">' . htmlspecialchars($part) . '</span>';
                        } else {
                            echo htmlspecialchars($part);
                        }
                    endforeach; 
                    ?>
                </h1>
                <p><?php echo htmlspecialchars($hero_section['subtitle'] ?? 'Spanduk, banner, stiker, baliho, brosur, dan berbagai kebutuhan cetak lainnya dengan kualitas Terbaik dan harga terjangkau.'); ?></p>
                <div class="hero-buttons">
                    <a href="#produk" class="btn-primary">Lihat Produk</a>
                    <a href="#kontak" class="btn-primary">Kontak Kami</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="swiper myHeroSlider">
                    <div class="swiper-wrapper">
                        <?php
                        // Ambil slides dari database
                        $hero_slides = $conn->query("SELECT * FROM website_hero_slides WHERE is_active = 1 ORDER BY sort_order");
                        if ($hero_slides && $hero_slides->num_rows > 0) {
                            while($slide = $hero_slides->fetch_assoc()) {
                                echo '<div class="swiper-slide">';
                                echo '<img src="' . $slide['image_path'] . '" alt="' . htmlspecialchars($slide['title']) . '">';
                                echo '</div>';
                            }
                        } else {
                            // Fallback ke gambar default
                            echo '<div class="swiper-slide">';
                            echo '<img src="assets/heroslide1.jpg" alt="Slide 1">';
                            echo '</div>';
                            echo '<div class="swiper-slide">';
                            echo '<img src="assets/heroslide2.jpg" alt="Slide 2">';
                            echo '</div>';
                            echo '<div class="swiper-slide">';
                            echo '<img src="assets/heroslide3.jpg" alt="Slide 3">';
                            echo '</div>';
                        }
                        ?>
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
                <?php if (!empty($services)): ?>
                    <?php foreach($services as $service): ?>
                    <div class="produk-item">
                        <div class="produk-icon"><i class="<?php echo $service['icon']; ?>"></i></div>
                        <h3><?php echo htmlspecialchars($service['title']); ?></h3>
                        <p><?php echo htmlspecialchars($service['description']); ?></p>
                        <button class="btn-order" data-produk="<?php echo htmlspecialchars($service['title']); ?>">
                            <?php echo htmlspecialchars($service['button_text'] ?? 'Pesan Sekarang'); ?>
                        </button>
                        <a href="produk.php?produk=<?php echo urlencode($service['title']); ?>" class="btn-view-design">Lihat Desain</a>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback jika tidak ada layanan -->
                    <div class="produk-item">
                        <div class="produk-icon"><i class="fas fa-flag"></i></div>
                        <h3>Spanduk & Banner</h3>
                        <p>Cetak spanduk dan banner dengan berbagai ukuran dan bahan berkualitas untuk kebutuhan promosi Anda.</p>
                        <button class="btn-order" data-produk="Spanduk & Banner">Pesan Sekarang</button>
                        <a href="produk.php?produk=Spanduk %26 Banner" class="btn-view-design">Lihat Desain</a>
                    </div>
                    <div class="produk-item">
                        <div class="produk-icon"><i class="fas fa-sticky-note"></i></div>
                        <h3>Stiker & Label</h3>
                        <p>Stiker berkualitas untuk produk, kemasan, atau promosi dengan berbagai pilihan bahan ternama dan finishing.</p>
                        <button class="btn-order" data-produk="Stiker & Label">Pesan Sekarang</button>
                        <a href="produk.php?produk=Stiker %26 Label" class="btn-view-design">Lihat Desain</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="cara-kerja" id="cara-kerja">
        <div class="container">
            <h2 class="section-title">Cara Memesan di <span class="highlight">King Printing</span></h2>
            <div class="steps">
                <?php if (!empty($steps)): ?>
                    <?php foreach($steps as $index => $step): ?>
                    <div class="step">
                        <div class="step-number"><?php echo $index + 1; ?></div>
                        <div class="step-icon"><i class="<?php echo $step['icon']; ?>"></i></div>
                        <h3><?php echo htmlspecialchars($step['title']); ?></h3>
                        <p><?php echo htmlspecialchars($step['description']); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback jika tidak ada steps -->
                    <div class="step">
                        <div class="step-number">1</div>
                        <div class="step-icon"><i class="fas fa-mouse-pointer"></i></div>
                        <h3>Pilih Produk</h3>
                        <p>Pilih produk dan klik tombol "Pesan Sekarang" untuk membuka form pemesanan.</p>
                    </div>
                    <div class="step">
                        <div class="step-number">2</div>
                        <div class="step-icon"><i class="fas fa-ruler-combined"></i></div>
                        <h3>Isi Form</h3>
                        <p>Lengkapi detail pesanan Anda pada form yang muncul, seperti ukuran, bahan, dan jumlah.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <section class="keunggulan">
        <div class="container">
            <h2 class="section-title">Mengapa Memilih <span class="highlight">King Printing</span>?</h2>
            <div class="keunggulan-grid">
                <?php if (!empty($features)): ?>
                    <?php foreach($features as $feature): ?>
                    <div class="keunggulan-item">
                        <div class="keunggulan-icon"><i class="<?php echo $feature['icon']; ?>"></i></div>
                        <h3><?php echo htmlspecialchars($feature['title']); ?></h3>
                        <p><?php echo htmlspecialchars($feature['description']); ?></p>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <!-- Fallback jika tidak ada features -->
                    <div class="keunggulan-item">
                        <div class="keunggulan-icon"><i class="fas fa-crown"></i></div>
                        <h3>Kualitas Terbaik</h3>
                        <p>Hasil cetak dengan kualitas terbaik.</p>
                    </div>
                    <div class="keunggulan-item">
                        <div class="keunggulan-icon"><i class="fas fa-bolt"></i></div>
                        <h3>Proses Cepat</h3>
                        <p>Pengerjaan cepat tanpa mengorbankan kualitas.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- MULAI SECTION FAQ (LAYOUT BARU: 2 KOLOM) -->
    <section class="faq-section" id="faq">
        <div class="container">
            <div class="faq-wrapper">
                <div class="faq-sidebar">
                    <div class="faq-header-block">
                        <span class="sub-title">Tanya Jawab</span>
                        <h2 class="section-title-left">Pertanyaan <span class="highlight">Umum</span></h2>
                        <p class="faq-desc">Berikut adalah beberapa hal yang sering ditanyakan oleh pelanggan kami. Klik pada pertanyaan untuk melihat jawabannya.</p>
                    </div>
                    <div class="help-card">
                        <div class="help-icon"><i class="fab fa-whatsapp"></i></div>
                        <div class="help-text">
                            <h4>Masih Bingung?</h4>
                            <p>Jangan ragu untuk konsultasi langsung dengan admin kami.</p>
                            <a href="https://wa.me/6288705844251?text=Halo%2C%20saya%20mau%20tanya%20tentang%20percetakan" target="_blank" class="btn-help">Chat Admin Sekarang <i class="fas fa-arrow-right"></i></a>
                        </div>
                    </div>
                </div>
                <div class="faq-list">
                    <?php if (!empty($faqs)): ?>
                        <?php foreach($faqs as $faq): ?>
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3><?php echo htmlspecialchars($faq['title']); ?></h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p><?php echo htmlspecialchars($faq['description']); ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <!-- Fallback jika tidak ada FAQ -->
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3>Berapa lama proses pengerjaan pesanan?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Waktu pengerjaan tergantung jenis produk dan jumlah pesanan. Untuk spanduk/banner standar biasanya selesai dalam 1-2 hari kerja.</p>
                            </div>
                        </div>
                        <div class="faq-item">
                            <div class="faq-question">
                                <h3>Apakah bisa bantu buatkan desainnya?</h3>
                                <i class="fas fa-chevron-down"></i>
                            </div>
                            <div class="faq-answer">
                                <p>Tentu saja! Kami memiliki tim desainer profesional yang siap membantu.</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </section>
    <!-- AKHIR SECTION FAQ -->

    <!-- Modal dan Footer tetap sama -->
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

    <footer class="footer" id="kontak">
    <div class="container">
        <div class="map-container">
            <?php
            // Ambil data kontak dari database
            $contact_data = $conn->query("SELECT * FROM website_sections WHERE section_name = 'contact'");
            if ($contact_data && $contact_data->num_rows > 0) {
                $contact = $contact_data->fetch_assoc();
                echo $contact['additional_info'] ?? '<iframe class="map-iframe" src="https://www.google.com/maps/embed?pb=!4v1762266865140!6m8!1m7!1sWhc2abtYaDzUcfT_F7LnHg!2m2!1d-0.4730531793778449!2d117.1653334981797!3f326.50841155499506!4f-15.840080292808594!5f1.0886293032444474" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>';
            }
            ?>
        </div>
        <div class="footer-content">
            <div class="footer-section">
                <div class="footer-logo"><h3>King Printing</h3></div>
                <p>Solusi cetak terpercaya untuk berbagai kebutuhan promosi dan bisnis Anda dengan kualitas Terbaik.</p>
            </div>
            <div class="footer-section">
                <h4>Kontak Kami</h4>
                <div class="contact-info">
                    <?php if (isset($contact)): ?>
                        <p><i class="fas fa-phone"></i> <?php echo htmlspecialchars($contact['title']); ?></p>
                        <p><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($contact['subtitle']); ?></p>
                        <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($contact['description']); ?></p>
                    <?php endif; ?>
                </div>
            </div>
            <div class="footer-section">
                <h4>Jam Operasional</h4>
                <div class="operational-hours">
                    <?php
                    // Ambil jam operasional dari database
                    $hours_result = $conn->query("SELECT * FROM operational_hours ORDER BY sort_order");
                    if ($hours_result && $hours_result->num_rows > 0) {
                        while($hour = $hours_result->fetch_assoc()) {
                            echo '<p>' . htmlspecialchars($hour['hours_text']) . '</p>';
                        }
                    } else {
                        // Fallback jika tidak ada data
                        echo '<p>Senin - Sabtu: 09.00 - 18.00</p>';
                        echo '<p>Minggu: Tutup</p>';
                    }
                    ?>
                </div>
            </div>
        </div>
        <div class="footer-bottom">
            <p><?php echo htmlspecialchars($contact['meta_data'] ?? '© 2025 King Printing. All rights reserved.'); ?></p>
        </div>
    </div>
</footer>

    <a href="https://wa.me/6288705844251?text=Halo%20kak%2C%20saya%20ingin%20melakukan%20pemesanan" class="whatsapp-sticky" target="_blank" data-tooltip="Pesan via WhatsApp"><i class="fab fa-whatsapp"></i></a>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>