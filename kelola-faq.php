<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Get FAQ data
$faqs = [];
$result = $conn->query("SELECT * FROM website_items WHERE section_name = 'faq' AND item_type = 'faq' ORDER BY sort_order");
if ($result) {
    while($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_faq'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        
        $max_order = $conn->query("SELECT MAX(sort_order) as max_order FROM website_items WHERE section_name = 'faq'")->fetch_assoc()['max_order'] ?? 0;
        $new_order = $max_order + 1;
        
        $sql = "INSERT INTO website_items (section_name, item_type, title, description, sort_order) 
                VALUES ('faq', 'faq', '$title', '$description', $new_order)";
        if ($conn->query($sql)) {
            $success = "FAQ berhasil ditambahkan!";
            header("Location: kelola-faq.php");
            exit;
        }
    }
    
    if (isset($_POST['update_order'])) {
        foreach ($_POST['faq_order'] as $faq_id => $order) {
            $conn->query("UPDATE website_items SET sort_order = " . intval($order) . " WHERE id = " . intval($faq_id));
        }
        $success = "Urutan berhasil diperbarui!";
        header("Location: kelola-faq.php");
        exit;
    }
    
    if (isset($_POST['delete_faq'])) {
        $faq_id = intval($_POST['faq_id']);
        $conn->query("DELETE FROM website_items WHERE id = $faq_id");
        $success = "FAQ berhasil dihapus!";
        header("Location: kelola-faq.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola FAQ - King Printing</title>
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

        .faq-list {
            display: grid;
            gap: 1rem;
        }

        .faq-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
        }

        .faq-question {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .faq-answer {
            color: #666;
            line-height: 1.5;
        }

        .faq-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .faq-actions span {
            font-weight: 500;
            color: #333;
        }

        .faq-actions input {
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
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="main-header">
            <h1>Kelola FAQ</h1>
            <a href="kelola-tampilan.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali 
            </a>
        </header>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Form Tambah FAQ -->
        <div class="form-section">
            <h2 class="section-title">Tambah FAQ Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Pertanyaan</label>
                    <input type="text" name="title" class="form-control" placeholder="Berapa lama proses pengerjaan?" required>
                </div>
                <div class="form-group">
                    <label>Jawaban</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Jawaban untuk pertanyaan..." required></textarea>
                </div>
                <button type="submit" name="add_faq" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tambah FAQ
                </button>
            </form>
        </div>

        <!-- Daftar FAQ -->
        <div class="form-section">
            <h2 class="section-title">Pertanyaan Umum (<?php echo count($faqs); ?>)</h2>
            
            <?php if (empty($faqs)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">Belum ada FAQ.</p>
            <?php else: ?>
                <!-- Form untuk Update Order -->
                <form method="POST" id="orderForm">
                    <div class="faq-list">
                        <?php foreach ($faqs as $faq): ?>
                        <div class="faq-card">
                            <div class="faq-question"><?php echo $faq['title']; ?></div>
                            <div class="faq-answer"><?php echo $faq['description']; ?></div>
                            <div class="faq-actions">
                                <span>Urutan: 
                                    <input type="number" name="faq_order[<?php echo $faq['id']; ?>]" 
                                        value="<?php echo $faq['sort_order']; ?>" min="1" style="width: 60px;">
                                </span>
                                
                                <!-- FORM TERPISAH UNTUK DELETE -->
                                <form method="POST" style="display: inline;">
                                    <button type="submit" name="delete_faq" class="btn btn-danger" 
                                            onclick="return confirm('Hapus FAQ ini?')">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
                                    <input type="hidden" name="faq_id" value="<?php echo $faq['id']; ?>">
                                </form>
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