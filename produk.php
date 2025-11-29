<?php
require_once 'config.php';

$produk = trim(urldecode($_GET['produk'] ?? ''));

if (empty($produk)) {
    header("Location: index.php");
    exit;
}

$stmt = $conn->prepare("SELECT id, gambar, caption FROM desain_produk WHERE produk = ? ORDER BY id DESC");
$stmt->bind_param("s", $produk);
$stmt->execute();
$desains = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog <?php echo htmlspecialchars($produk); ?></title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* CSS KHUSUS HALAMAN INI */
        
        /* Grid System */
        .desain-gallery {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(240px, 1fr));
            gap: 40px;
            margin-top: 2rem;
            padding-bottom: 50px; 
        }

        /* 1. KARTU UTAMA */
        .desain-item {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: relative; 
            overflow: hidden !important;
            transition: transform 0.2s; 
        }

        .desain-item:hover {
            transform: translateY(-5px);
        }

        /* 2. PEMBUNGKUS THUMBNAIL */
        .img-wrapper {
            width: 100%;
            height: 180px; 
            overflow: hidden;
            border-radius: 12px 12px 0 0;
            background: white; 
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 5px;
            box-sizing: border-box;
            border-bottom: 1px solid #f9f9f9;
        }

        /* GAMBAR THUMBNAIL */
        .thumb-img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        /* Caption */
        .desain-caption {
            padding: 15px;
            text-align: center; 
            font-weight: 600; 
            color: #444; 
            font-size: 0.95rem;
        }

        .btn-back { 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            text-decoration: none; 
            color: #555; 
            font-weight: 600; 
            margin-bottom: 25px; 
        }
        .btn-back:hover { 
            color: var(--secondary-red); 
        }
    </style>
</head>
<body>

    <header class="header">
        <div class="container">
            <div class="logo">
                <img src="assets/crown-logo2.png" alt="Logo" class="logo-image">
                <div class="logo-text"><span>King Printing</span></div>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Kembali ke Beranda</a></li>
                </ul>
            </nav>
        </div>
    </header>

    <section style="padding: 8rem 0 5rem; background: #fcfcfc; min-height: 100vh;">
        <div class="container">
            <h2 class="section-title">Katalog <span class="highlight"><?php echo htmlspecialchars($produk); ?></span></h2>
            
            <?php if (count($desains) > 0): ?>
                <div class="desain-gallery">
                    <?php foreach ($desains as $d): ?>
                        <div class="desain-item">
                            
                            <div class="img-wrapper">
                                <img src="<?= htmlspecialchars($d['gambar']) ?>" class="thumb-img" alt="Thumbnail">
                            </div>

                            <div class="desain-caption">
                                <?= htmlspecialchars($d['caption'] ?: 'Desain Terbaru') ?>
                            </div>

                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div style="text-align:center; padding:50px; color:#aaa;">
                    <i class="fas fa-images" style="font-size:3rem; margin-bottom:10px;"></i>
                    <p>Belum ada foto.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>
    
    <footer class="footer" style="padding:20px 0; text-align:center; font-size:0.9rem;">
        <p>&copy; 2025 King Printing.</p>
    </footer>

</body>
</html>