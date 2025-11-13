<?php
session_start();
require_once 'config.php';

$is_admin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true;
$produk = trim(urldecode($_GET['produk'] ?? ''));
if (empty($produk)) {
    header("Location: index.html");
    exit;
}

$message = '';

// === TAMBAH DESAIN ===
if ($is_admin && isset($_POST['action']) && $_POST['action'] === 'tambah') {
    $caption = trim($_POST['caption'] ?? '');
    $uploadDir = 'assets/desain/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    if (!empty($_FILES['gambar']['name'])) {
        $fileName = time() . '_' . basename($_FILES['gambar']['name']);
        $targetFile = $uploadDir . $fileName;

        if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
            $stmt = $conn->prepare("INSERT INTO desain_produk (produk, gambar, caption) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $produk, $targetFile, $caption);
            $stmt->execute();
            $stmt->close();
            $message = "<div class='success-msg'>Desain berhasil ditambahkan!</div>";
        } else {
            $message = "<div class='error-msg'>Gagal upload gambar.</div>";
        }
    }
}

// === EDIT CAPTION ===
if ($is_admin && isset($_POST['action']) && $_POST['action'] === 'edit' && isset($_POST['id'])) {
    $id = (int)$_POST['id'];
    $caption = trim($_POST['caption'] ?? '');
    $stmt = $conn->prepare("UPDATE desain_produk SET caption = ? WHERE id = ? AND produk = ?");
    $stmt->bind_param("sis", $caption, $id, $produk);
    $stmt->execute();
    $stmt->close();
    $message = "<div class='success-msg'>Caption diperbarui!</div>";
}

// === HAPUS DESAIN ===
if ($is_admin && isset($_GET['hapus']) && is_numeric($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("SELECT gambar FROM desain_produk WHERE id = ? AND produk = ?");
    $stmt->bind_param("is", $id, $produk);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) @unlink($row['gambar']);
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM desain_produk WHERE id = ? AND produk = ?");
    $stmt->bind_param("is", $id, $produk);
    $stmt->execute();
    $stmt->close();
    $message = "<div class='success-msg'>Desain dihapus!</div>";
    header("Location: produk.php?produk=" . urlencode($produk));
    exit;
}

// === AMBIL DESAIN DARI DATABASE ===
$stmt = $conn->prepare("SELECT id, gambar, caption FROM desain_produk WHERE produk = ? ORDER BY id DESC");
$stmt->bind_param("s", $produk);
$stmt->execute();
$result = $stmt->get_result();
$desains = $result->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// === SIAPKAN LOGIKA PRODUK KHUSUS ===
$produk_clean = strtolower($produk);
$has_desain = count($desains) > 0;
$is_special_product = in_array($produk_clean, ['spanduk', 'banner', 'stiker', 'kemasan', 'dus', 'custom']);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($produk); ?> - King Printing</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <!-- HEADER -->
    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="assets/crown-logo2.png" alt="King Printing Logo" class="logo-image">
                <div class="logo-text"><span>King Printing</span></div>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.html#home">Beranda</a></li>
                    <li><a href="index.html#produk">Produk</a></li>
                    <li><a href="index.html#cara-kerja">Cara Pesan</a></li>
                    <li><a href="index.html#tentang">Tentang Kami</a></li>
                    <li><a href="index.html#kontak">Kontak</a></li>
                    <?php if ($is_admin): ?>
                        <li><a href="dashboard.php" class="login-icon">Admin</a></li>
                    <?php else: ?>
                        <li><a href="#" id="loginBtn" class="login-icon">Login</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
        </div>
    </header>

    <section class="produk-detail" style="padding: 10rem 0 5rem; background: #f9f9f9;">
        <div class="container">
            <h2 class="section-title">Desain <span class="highlight"><?php echo htmlspecialchars($produk); ?></span></h2>

            <p>Contoh desain untuk inspirasi. Anda juga bisa kirim desain sendiri!</p>

            <?php echo $message; ?>

            <!-- Admin: Tambah Desain -->
            <div class="admin-only" style="display: <?php echo $is_admin ? 'block' : 'none'; ?>;">
                <form method="POST" enctype="multipart/form-data" style="margin:1.5rem 0; padding:1.2rem; background:#f0f0f0; border-radius:12px; display:flex; gap:0.6rem; flex-wrap:wrap;">
                    <input type="hidden" name="action" value="tambah">
                    <input type="text" name="caption" placeholder="Judul desain (opsional)" style="flex:1; min-width:220px; padding:0.7rem; border-radius:8px; border:1px solid #ddd;">
                    <input type="file" name="gambar" accept="image/*" required style="flex:1; min-width:220px;">
                    <button type="submit" style="background:var(--brand-red); color:white; border:none; padding:0.7rem 1.2rem; border-radius:8px; font-weight:600; cursor:pointer;">+ Tambah Desain</button>
                </form>
            </div>

            <!-- SLIDER DESAIN DARI DATABASE -->
            <?php if ($has_desain): ?>
                <div class="slider-container">
                    <div class="slider-wrapper">
                        <?php foreach ($desains as $index => $d): ?>
                            <div class="slider-item">
                                <img src="<?= htmlspecialchars($d['gambar']) ?>" alt="<?= htmlspecialchars($d['caption']) ?>">
                                <div class="slider-caption"><?= htmlspecialchars($d['caption'] ?: 'Desain ' . ($index + 1)) ?></div>

                                <!-- Admin: Edit & Hapus -->
                                <div class="action-icons admin-only" style="position:absolute; top:18px; right:18px; background:rgba(255,255,255,0.95); padding:0.5rem; border-radius:10px; z-index:10;">
                                    <a href="javascript:void(0)" onclick="toggleEdit(<?= $d['id'] ?>)" style="color:#f39c12; margin:0 0.4rem;">
                                        Edit
                                    </a>
                                    <a href="?produk=<?= urlencode($produk) ?>&hapus=<?= $d['id'] ?>" onclick="return confirm('Hapus?')" style="color:#e74c3c; margin:0 0.4rem;">
                                        Delete
                                    </a>
                                </div>

                                <div id="edit-<?= $d['id'] ?>" class="edit-form admin-only" style="display:none; margin-top:1.2rem; padding:1rem; background:white; border:1px solid #eee; border-radius:10px;">
                                    <form method="POST" style="display:flex; gap:0.5rem;">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <input type="text" name="caption" value="<?= htmlspecialchars($d['caption']) ?>" placeholder="Edit judul" style="flex:1; padding:0.6rem; border-radius:8px; border:1px solid #ddd;">
                                        <button type="submit" style="background:#f39c12; color:white; border:none; padding:0.6rem 1rem; border-radius:8px;">Simpan</button>
                                        <button type="button" onclick="toggleEdit(<?= $d['id'] ?>)" style="background:#95a5a6; color:white; border:none; padding:0.6rem 1rem; border-radius:8px;">Batal</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <button class="slider-prev">Previous</button>
                    <button class="slider-next">Next</button>

                    <div class="slider-dots">
                        <?php foreach ($desains as $index => $d): ?>
                            <span class="dot" data-index="<?= $index ?>"></span>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- PESAN "BELUM ADA CONTOH..." HANYA UNTUK PRODUK BIASA -->
            <?php if (!$has_desain && !$is_special_product): ?>
                <p class="no-design">Belum ada contoh desain untuk produk ini.</p>
            <?php endif; ?>

            <!-- CONTOH STATIS UNTUK PRODUK KHUSUS -->
            <?php if ($is_special_product): ?>
                <!-- Spanduk & Banner -->
                <?php if (in_array($produk_clean, ['spanduk', 'banner'])): ?>
                    <div class="banner-section" style="margin-top:3rem;">
                        <h3 style="text-align:center; color:#333;">Contoh Desain Banner & Spanduk</h3>
                        <div style="display:flex; flex-direction:column; align-items:center; gap:2rem; margin-top:2rem;">
                            <div style="text-align:center;">
                                <img src="./assets/desain/spanduk1.jpg" alt="Desain Spanduk 1" 
                                     style="width:80%; max-width:700px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                                <p style="margin-top:0.5rem; font-weight:500;">Spanduk Promosi Umum</p>
                            </div>
                            <div style="text-align:center;">
                                <img src="./assets/desain/spanduk2.png" alt="Desain Spanduk 2" 
                                     style="width:80%; max-width:700px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                                <p style="margin-top:0.5rem; font-weight:500;">Spanduk Event / Kegiatan</p>
                            </div>
                            <div style="text-align:center;">
                                <img src="./assets/desain/banner_anak.jpg" alt="Desain Banner Anak" 
                                     style="width:80%; max-width:700px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                                <p style="margin-top:0.5rem; font-weight:500;">Banner Hari Anak Nasional</p>
                            </div>
                            <div style="text-align:center;">
                                <img src="./assets/desain/banner_mobil.png" alt="Desain Banner Mobil" 
                                     style="width:80%; max-width:700px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                                <p style="margin-top:0.5rem; font-weight:500;">Banner Cuci Mobil & Auto Detailing</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Stiker -->
                <?php if ($produk_clean === 'stiker'): ?>
                    <div class="stiker-section" style="margin-top:3rem;">
                        <h3 style="text-align:center; color:#333;">Contoh Desain Stiker</h3>
                        <div style="display:flex; flex-direction:column; align-items:center; gap:2rem; margin-top:2rem;">
                            <div style="text-align:center;">
                                <img src="./assets/desain/sticker.jpg" alt="Desain Stiker Amanah Music & Sound System" 
                                     style="width:80%; max-width:700px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                                <p style="margin-top:0.5rem; font-weight:500;">Stiker Promosi Musik & Sound System</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>

                <!-- Kemasan & Dus -->
                <?php if (in_array($produk_clean, ['kemasan', 'dus'])): ?>
                    <div class="kemasan-section" style="margin-top:3rem;">
                        <h3 style="text-align:center; color:#333;">Contoh Desain Kemasan & Dus</h3>
                        <div style="display:flex; flex-direction:column; align-items:center; gap:2rem; margin-top:2rem;">
                            <div style="text-align:center;">
                                <img src="./assets/desain/kemasan.jpg" alt="Desain Kemasan Cookies" 
                                     style="width:80%; max-width:700px; border-radius:12px; box-shadow:0 4px 10px rgba(0,0,0,0.15);">
                                <p style="margin-top:0.5rem; font-weight:500;">Label Kemasan Cookies</p>
                            </div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </section>

    <!-- FOOTER -->
    <footer class="footer" id="kontak">
        <div class="container">
            <div class="map-container"><iframe class="map-iframe" src="https://www.google.com/maps/embed?pb=!4v1762266865140!6m8!1m7!1sWhc2abtYaDzUcfT_F7LnHg!2m2!1d-0.4730531793778449!2d117.1653334981797!3f326.50841155499506!4f-15.840080292808594!5f1.0886293032444474" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe></div>
            <div class="footer-bottom"><p>© 2025 King Advertising. All rights reserved.</p></div>
        </div>
    </footer>

    <script src="script.js"></script>
    <script>
        function toggleEdit(id) {
            const el = document.getElementById('edit-' + id);
            el.style.display = el.style.display === 'block' ? 'none' : 'block';
        }

        document.addEventListener('DOMContentLoaded', () => {
            const wrapper = document.querySelector('.slider-wrapper');
            const items = document.querySelectorAll('.slider-item');
            const prev = document.querySelector('.slider-prev');
            const next = document.querySelector('.slider-next');
            const dots = document.querySelectorAll('.dot');
            let index = 0;
            const total = items.length;

            function show(n) {
                index = (n + total) % total;
                wrapper.style.transform = `translateX(-${index * 100}%)`;
                dots.forEach((d, i) => d.classList.toggle('active', i === index));
            }

            if (total > 0) {
                next.addEventListener('click', () => show(index + 1));
                prev.addEventListener('click', () => show(index - 1));
                dots.forEach(d => d.addEventListener('click', () => show(parseInt(d.dataset.index))));
                show(0);
            }
        });
    </script>
</body>
</html>