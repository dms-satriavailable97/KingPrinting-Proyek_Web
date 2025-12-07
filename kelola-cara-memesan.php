<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Get steps data
$steps = [];
$result = $conn->query("SELECT * FROM website_items WHERE section_name = 'steps' AND item_type = 'step' ORDER BY sort_order");
if ($result) {
    while($row = $result->fetch_assoc()) {
        $steps[] = $row;
    }
}

// Icon list
$icon_list = [
    'fas fa-mouse-pointer', 'fas fa-ruler-combined', 'fab fa-whatsapp', 'fas fa-shipping-fast',
    'fas fa-phone', 'fas fa-envelope', 'fas fa-credit-card', 'fas fa-check-circle',
    'fas fa-user', 'fas fa-clipboard-list', 'fas fa-palette', 'fas fa-print'
];

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_step'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $icon = $conn->real_escape_string($_POST['icon']);
        
        $max_order = $conn->query("SELECT MAX(sort_order) as max_order FROM website_items WHERE section_name = 'steps'")->fetch_assoc()['max_order'] ?? 0;
        $new_order = $max_order + 1;
        
        $sql = "INSERT INTO website_items (section_name, item_type, title, description, icon, sort_order) 
                VALUES ('steps', 'step', '$title', '$description', '$icon', $new_order)";
        if ($conn->query($sql)) {
            $success = "Langkah berhasil ditambahkan!";
            header("Location: kelola-cara-memesan.php");
            exit;
        }
    }
    
    if (isset($_POST['update_order'])) {
        foreach ($_POST['step_order'] as $step_id => $order) {
            $conn->query("UPDATE website_items SET sort_order = " . intval($order) . " WHERE id = " . intval($step_id));
        }
        $success = "Urutan berhasil diperbarui!";
        header("Location: kelola-cara-memesan.php");
        exit;
    }
    
    if (isset($_POST['delete_step'])) {
        $step_id = intval($_POST['step_id']);
        $conn->query("DELETE FROM website_items WHERE id = $step_id");
        $success = "Langkah berhasil dihapus!";
        header("Location: kelola-cara-memesan.php");
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Cara Memesan - King Printing</title>
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

        .steps-grid {
            display: grid;
            gap: 1rem;
        }

        .step-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            display: flex;
            gap: 1rem;
            align-items: flex-start;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: #9a2020;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .step-icon {
            width: 50px;
            height: 50px;
            background: #FFD700;
            color: #9a2020;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            flex-shrink: 0;
        }

        .step-info {
            flex: 1;
        }

        .step-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .step-description {
            color: #666;
            line-height: 1.5;
        }

        .step-actions {
            margin-top: 1rem;
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .step-actions span {
            font-weight: 500;
            color: #333;
        }

        .step-actions input {
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

        /* Icon Picker */
        .icon-picker {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 10px;
            margin: 1rem 0;
            max-height: 200px;
            overflow-y: auto;
        }

        .icon-option {
            padding: 1rem;
            text-align: center;
            border: 2px solid #ddd;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .icon-option:hover {
            border-color: #9a2020;
        }

        .icon-option.selected {
            border-color: #9a2020;
            background: #fff3cd;
        }

        .icon-option i {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
            color: #9a2020;
        }

        .icon-option span {
            font-size: 0.7rem;
            font-family: 'Poppins', sans-serif;
        }

        /* === STYLES UNTUK MODAL KONFIRMASI (BARU) === */
        .custom-modal { 
            display: none; 
            position: fixed; 
            z-index: 9999; 
            left: 0; 
            top: 0; 
            width: 100%; 
            height: 100%; 
            background-color: rgba(0, 0, 0, 0.5); 
            backdrop-filter: blur(3px); 
            animation: fadeIn 0.2s ease-out; 
        }
        .custom-modal-content { 
            background-color: #fff; 
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            padding: 0; 
            border-radius: 12px; 
            width: 90%; 
            max-width: 400px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
            border-top: 6px solid #9a2020; 
            animation: slideIn 0.3s ease-out; 
        }
        .custom-modal-header { padding: 25px 20px 10px; text-align: center; }
        .custom-modal-icon { font-size: 3rem; color: #dc3545; margin-bottom: 15px; }
        .custom-modal-title { font-size: 1.4rem; font-weight: 600; color: #333; margin: 0; }
        .custom-modal-body { padding: 10px 25px 25px; text-align: center; color: #666; font-size: 0.95rem; line-height: 1.6; }
        .custom-modal-footer { 
            padding: 20px; 
            background: #f9f9f9; 
            border-top: 1px solid #eee; 
            border-radius: 0 0 12px 12px; 
            display: flex; 
            justify-content: center; 
            gap: 15px; 
        }
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translate(-50%, -70%); opacity: 0; } to { transform: translate(-50%, -50%); opacity: 1; } }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="main-header">
            <h1>Kelola Cara Memesan</h1>
            <a href="kelola-tampilan.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </header>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Form Tambah Langkah -->
        <div class="form-section">
            <h2 class="section-title">Tambah Langkah Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Judul Langkah</label>
                    <input type="text" name="title" class="form-control" placeholder="Pilih Produk" required>
                </div>
                <div class="form-group">
                    <label>Deskripsi</label>
                    <textarea name="description" class="form-control" rows="2" placeholder="Deskripsi langkah..." required></textarea>
                </div>
                <div class="form-group">
                    <label>Pilih Icon</label>
                    <div class="icon-picker">
                        <?php foreach($icon_list as $icon): ?>
                        <div class="icon-option" data-icon="<?php echo $icon; ?>">
                            <i class="<?php echo $icon; ?>"></i>
                            <span><?php echo str_replace('fas fa-', '', $icon); ?></span>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <input type="hidden" name="icon" id="selected_icon" required>
                </div>
                <button type="submit" name="add_step" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tambah Langkah
                </button>
            </form>
        </div>

        <!-- Daftar Langkah -->
        <div class="form-section">
            <h2 class="section-title">Langkah-langkah (<?php echo count($steps); ?>)</h2>
            
            <?php if (empty($steps)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">Belum ada langkah.</p>
            <?php else: ?>
                <!-- Form untuk Update Order -->
                <form method="POST" id="orderForm">
                    <div class="steps-grid">
                        <?php foreach($steps as $index => $step): ?>
                        <div class="step-card">
                            <div class="step-number"><?php echo $index + 1; ?></div>
                            <div class="step-icon">
                                <i class="<?php echo $step['icon']; ?>"></i>
                            </div>
                            <div class="step-info">
                                <div class="step-title"><?php echo $step['title']; ?></div>
                                <div class="step-description"><?php echo $step['description']; ?></div>
                                <div class="step-actions">
                                    <span>Urutan: 
                                        <input type="number" name="step_order[<?php echo $step['id']; ?>]" 
                                            value="<?php echo $step['sort_order']; ?>" min="1" style="width: 60px;">
                                    </span>
                                    
                                    <!-- TOMBOL HAPUS DENGAN TRIGGER MODAL -->
                                    <button type="button" class="btn btn-danger delete-trigger" 
                                            data-id="<?php echo $step['id']; ?>">
                                        <i class="fas fa-trash"></i> Hapus
                                    </button>
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

    <!-- MODAL CONFIRM HAPUS (POPUP TENGAH LAYAR) -->
    <div id="deleteModal" class="custom-modal">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <div class="custom-modal-icon">
                    <i class="fas fa-exclamation-triangle"></i>
                </div>
                <h3 class="custom-modal-title">Konfirmasi Hapus</h3>
            </div>
            <div class="custom-modal-body">
                <p>Apakah Anda yakin ingin menghapus langkah ini?</p>
            </div>
            <div class="custom-modal-footer">
                <button id="confirmDeleteBtn" class="btn btn-danger">Ya, Hapus</button>
                <button id="cancelDeleteBtn" class="btn btn-secondary">Batal</button>
            </div>
        </div>
    </div>

    <!-- FORM TERSEMBUNYI UNTUK HANDLE DELETE -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_step" value="1">
        <input type="hidden" name="step_id" id="modalStepId">
    </form>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Icon Picker
            document.querySelectorAll('.icon-option').forEach(option => {
                option.addEventListener('click', function() {
                    document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
                    this.classList.add('selected');
                    document.getElementById('selected_icon').value = this.dataset.icon;
                });
            });

            // Logic Modal Hapus
            const modal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDeleteBtn');
            const cancelBtn = document.getElementById('cancelDeleteBtn');
            const deleteForm = document.getElementById('deleteForm');
            const stepIdInput = document.getElementById('modalStepId');
            
            document.querySelectorAll('.delete-trigger').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.getAttribute('data-id');
                    stepIdInput.value = id;
                    modal.style.display = 'block';
                });
            });

            cancelBtn.addEventListener('click', function() {
                modal.style.display = 'none';
                stepIdInput.value = '';
            });

            window.addEventListener('click', function(e) {
                if (e.target == modal) {
                    modal.style.display = 'none';
                    stepIdInput.value = '';
                }
            });

            confirmBtn.addEventListener('click', function() {
                if(stepIdInput.value) {
                    deleteForm.submit();
                }
            });
        });
    </script>
</body>
</html>