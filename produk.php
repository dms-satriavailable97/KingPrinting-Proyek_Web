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

$produk_clean = strtolower($produk);
$has_desain = count($desains) > 0;
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

        <!-- SEMUA PRODUK SEKARANG GRID -->
        <?php if ($has_desain): ?>
            <div class="banner-section" style="margin-top:3rem;">
                <h3 style="text-align:center; color:#333;">Contoh Desain <?php echo htmlspecialchars($produk); ?></h3>
                <div class="desain-gallery" style="display:grid; grid-template-columns:repeat(auto-fit, minmax(250px, 1fr)); gap:1.5rem; margin-top:2rem;">
                    <?php foreach ($desains as $d): ?>
                        <div class="desain-item" style="background:white; border-radius:10px; overflow:hidden; box-shadow:0 4px 8px rgba(0,0,0,0.1); position:relative;">
                            <img src="<?= htmlspecialchars($d['gambar']) ?>" alt="<?= htmlspecialchars($d['caption']) ?>" style="width:100%; height:200px; object-fit:cover;">
                            <div class="desain-caption" style="padding:0.8rem; text-align:center; font-weight:500;">
                                <?= htmlspecialchars($d['caption'] ?: 'Desain Produk') ?>
                            </div>

                            <?php if ($is_admin): ?>
                                <div class="action-icons" style="position:absolute; top:10px; right:10px; background:rgba(255,255,255,0.9); border-radius:8px; padding:0.3rem 0.5rem;">
                                    <a href="javascript:void(0)" onclick="toggleEdit(<?= $d['id'] ?>)" style="color:#f39c12; margin:0 0.3rem;">Edit</a>
                                    <a href="?produk=<?= urlencode($produk) ?>&hapus=<?= $d['id'] ?>" onclick="return confirm('Hapus desain ini?')" style="color:#e74c3c; margin:0 0.3rem;">Hapus</a>
                                </div>

                                <div id="edit-<?= $d['id'] ?>" class="edit-form" style="display:none; margin:1rem; background:#f9f9f9; padding:0.8rem; border-radius:8px;">
                                    <form method="POST" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                                        <input type="hidden" name="action" value="edit">
                                        <input type="hidden" name="id" value="<?= $d['id'] ?>">
                                        <input type="text" name="caption" value="<?= htmlspecialchars($d['caption']) ?>" placeholder="Edit caption" style="flex:1; padding:0.5rem; border-radius:8px; border:1px solid #ccc;">
                                        <button type="submit" style="background:#f39c12; color:white; border:none; padding:0.5rem 1rem; border-radius:8px;">Simpan</button>
                                        <button type="button" onclick="toggleEdit(<?= $d['id'] ?>)" style="background:#95a5a6; color:white; border:none; padding:0.5rem 1rem; border-radius:8px;">Batal</button>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php else: ?>
            <p class="no-design">Belum ada contoh desain untuk produk ini.</p>
        <?php endif; ?>
    </div>
</section>

<footer class="footer" id="kontak">
    <div class="container">
        <div class="map-container">
            <iframe class="map-iframe" src="https://www.google.com/maps/embed?pb=!4v1762266865140!6m8!1m7!1sWhc2abtYaDzUcfT_F7LnHg!2m2!1d-0.4730531793778449!2d117.1653334981797!3f326.50841155499506!4f-15.840080292808594!5f1.0886293032444474" width="100%" height="450" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
        </div>
        <div class="footer-bottom"><p>© 2025 King Advertising. All rights reserved.</p></div>
    </div>
</footer>
    <script src="script.js"></script>

<script>
function toggleEdit(id) {
    const el = document.getElementById('edit-' + id);
    el.style.display = el.style.display === 'block' ? 'none' : 'block';
}
</script>
</body>
</html>
