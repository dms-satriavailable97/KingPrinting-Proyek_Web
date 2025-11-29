<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Get hero section data
$hero_section = [];
$result = $conn->query("SELECT * FROM website_sections WHERE section_name = 'hero'");
if ($result && $result->num_rows > 0) {
    $hero_section = $result->fetch_assoc();
}

// Get hero slides
$hero_slides = [];
$result = $conn->query("SELECT * FROM website_hero_slides ORDER BY sort_order");
if ($result) {
    while($row = $result->fetch_assoc()) {
        $hero_slides[] = $row;
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update_hero_text'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $subtitle = $conn->real_escape_string($_POST['subtitle']);
        
        if (!empty($hero_section)) {
            $sql = "UPDATE website_sections SET title = '$title', subtitle = '$subtitle' WHERE section_name = 'hero'";
        } else {
            $sql = "INSERT INTO website_sections (section_name, title, subtitle) VALUES ('hero', '$title', '$subtitle')";
        }
        
        if ($conn->query($sql)) {
            $success = "Teks hero berhasil diperbarui!";
            // Refresh data
            $result = $conn->query("SELECT * FROM website_sections WHERE section_name = 'hero'");
            if ($result && $result->num_rows > 0) {
                $hero_section = $result->fetch_assoc();
            }
        }
    }
    
    if (isset($_POST['add_slide'])) {
        // Upload gambar
        $image_path = '';
        if (isset($_FILES['slide_image']) && $_FILES['slide_image']['error'] === 0) {
            $upload_dir = 'assets/uploads/';
            if (!is_dir($upload_dir)) mkdir($upload_dir, 0755, true);
            
            $file_name = time() . '_' . basename($_FILES['slide_image']['name']);
            $target_file = $upload_dir . $file_name;
            
            if (move_uploaded_file($_FILES['slide_image']['tmp_name'], $target_file)) {
                $image_path = $target_file;
            }
        }
        
        $max_order = $conn->query("SELECT MAX(sort_order) as max_order FROM website_hero_slides")->fetch_assoc()['max_order'] ?? 0;
        $new_order = $max_order + 1;
        
        $sql = "INSERT INTO website_hero_slides (image_path, sort_order) VALUES ('$image_path', $new_order)";
        if ($conn->query($sql)) {
            $success = "Slide berhasil ditambahkan!";
            header("Location: kelola-hero.php");
            exit;
        }
    }
    
    if (isset($_POST['update_order'])) {
        foreach ($_POST['slide_order'] as $slide_id => $order) {
            $conn->query("UPDATE website_hero_slides SET sort_order = " . intval($order) . " WHERE id = " . intval($slide_id));
        }
        $success = "Urutan slide berhasil diperbarui!";
        header("Location: kelola-hero.php");
        exit;
    }
    
    if (isset($_POST['delete_slide'])) {
        $slide_id = intval($_POST['slide_id']);
        $conn->query("DELETE FROM website_hero_slides WHERE id = $slide_id");
        $success = "Slide berhasil dihapus!";
        header("Location: kelola-hero.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Hero - King Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: #f5f5f5;
            color: #333;
            font-family: 'Poppins', sans-serif;
        }

        .admin-container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }

        .main-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }

        .main-header h1 {
            color: #9a2020;
            font-size: 1.8rem;
            font-weight: 600;
        }

        .form-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        .section-title {
            color: #9a2020;
            margin-bottom: 1.5rem;
            border-bottom: 2px solid #FFD700;
            padding-bottom: 0.5rem;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #333;
        }

        .form-control {
            width: 100%;
            padding: 0.8rem;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
            font-size: 0.9rem;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-family: 'Poppins', sans-serif;
            font-weight: 500;
            font-size: 0.9rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #9a2020;
            color: white;
        }

        .btn-primary:hover {
            background: #7a1a1a;
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
        }

        .slides-grid {
            display: grid;
            gap: 1rem;
        }

        .slide-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .slide-preview {
            width: 200px;
            height: 120px;
            border-radius: 5px;
            overflow: hidden;
            flex-shrink: 0;
        }

        .slide-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .slide-info {
            flex: 1;
        }

        .slide-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .slide-actions span {
            font-weight: 500;
            color: #333;
        }

        .slide-actions input {
            width: 60px;
            padding: 0.3rem;
            border: 1px solid #ddd;
            border-radius: 3px;
            text-align: center;
        }

        .alert {
            padding: 1rem;
            margin-bottom: 1rem;
            border-radius: 5px;
            font-family: 'Poppins', sans-serif;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .help-text {
            font-size: 0.8rem;
            color: #666;
            margin-top: 0.3rem;
            font-family: 'Poppins', sans-serif;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="main-header">
            <h1>Kelola Hero Section</h1>
            <a href="kelola-tampilan.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali 
            </a>
        </header>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Form Edit Teks Hero -->
        <div class="form-section">
            <h2 class="section-title">Edit Teks Hero</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Judul Hero</label>
                    <input type="text" name="title" class="form-control" 
                           value="<?php echo htmlspecialchars($hero_section['title'] ?? 'Raja di Dunia {highlight}Promosi & Advertising{/highlight}'); ?>" 
                           required>
                    <div class="help-text">Gunakan {highlight}teks{/highlight} untuk menandai teks yang ingin di-highlight</div>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi Hero</label>
                    <textarea name="subtitle" class="form-control" rows="3" required><?php echo htmlspecialchars($hero_section['subtitle'] ?? 'Spanduk, banner, stiker, baliho, brosur, dan berbagai kebutuhan cetak lainnya dengan kualitas kerajaan dan harga terjangkau.'); ?></textarea>
                </div>

                <button type="submit" name="update_hero_text" class="btn btn-success">
                    <i class="fas fa-save"></i> Simpan Teks Hero
                </button>
            </form>
        </div>

        <!-- Form Tambah Slide -->
        <div class="form-section">
            <h2 class="section-title">Tambah Slide Baru</h2>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Upload Gambar Slide</label>
                    <input type="file" name="slide_image" accept="image/*" class="form-control" required>
                </div>

                <button type="submit" name="add_slide" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tambah Slide
                </button>
            </form>
        </div>

        <!-- Daftar Slides -->
        <div class="form-section">
            <h2 class="section-title">Kelola Slides (<?php echo count($hero_slides); ?>)</h2>
            
            <?php if (empty($hero_slides)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">Belum ada slides.</p>
            <?php else: ?>
                <!-- Form untuk Update Order -->
                <form method="POST" id="orderForm">
                    <div class="slides-grid">
                        <?php foreach ($hero_slides as $slide): ?>
                        <div class="slide-card">
                            <div class="slide-preview">
                                <?php if ($slide['image_path'] && file_exists($slide['image_path'])): ?>
                                    <img src="<?php echo $slide['image_path']; ?>" alt="Slide <?php echo $slide['id']; ?>">
                                <?php else: ?>
                                    <div style="display: flex; align-items: center; justify-content: center; height: 100%; background: #e9ecef; color: #999;">
                                        <i class="fas fa-image fa-2x"></i>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <div class="slide-info">
                                <div class="slide-actions">
                                    <span>Urutan: 
                                        <input type="number" name="slide_order[<?php echo $slide['id']; ?>]" 
                                            value="<?php echo $slide['sort_order']; ?>" min="1" style="width: 60px;">
                                    </span>
                                    
                                    <!-- FORM TERPISAH UNTUK DELETE -->
                                    <form method="POST" style="display: inline;">
                                        <button type="submit" name="delete_slide" class="btn btn-danger" 
                                                onclick="return confirm('Hapus slide ini?')">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                        <input type="hidden" name="slide_id" value="<?php echo $slide['id']; ?>">
                                    </form>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 1rem; text-align: right;">
                        <button type="submit" name="update_order" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Urutan
                        </button>
                    </div>
                </form>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>