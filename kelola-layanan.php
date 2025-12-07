<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// Get services data
$services = [];
$result = $conn->query("SELECT * FROM website_items WHERE section_name = 'services' AND item_type = 'service' ORDER BY sort_order");
if ($result) {
    while($row = $result->fetch_assoc()) {
        $services[] = $row;
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_service'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        $icon = $conn->real_escape_string($_POST['icon']);
        $button_text = $conn->real_escape_string($_POST['button_text']);
        
        $max_order = $conn->query("SELECT MAX(sort_order) as max_order FROM website_items WHERE section_name = 'services'")->fetch_assoc()['max_order'] ?? 0;
        $new_order = $max_order + 1;
        
        $sql = "INSERT INTO website_items (section_name, item_type, title, description, icon, button_text, sort_order) 
                VALUES ('services', 'service', '$title', '$description', '$icon', '$button_text', $new_order)";
        if ($conn->query($sql)) {
            $success = "Layanan berhasil ditambahkan!";
            header("Location: kelola-layanan.php");
            exit;
        }
    }
    
    if (isset($_POST['update_order'])) {
        foreach ($_POST['service_order'] as $service_id => $order) {
            $conn->query("UPDATE website_items SET sort_order = " . intval($order) . " WHERE id = " . intval($service_id));
        }
        $success = "Urutan berhasil diperbarui!";
        header("Location: kelola-layanan.php");
        exit;
    }
    
    if (isset($_POST['delete_service'])) {
        $service_id = intval($_POST['service_id']);
        $conn->query("DELETE FROM website_items WHERE id = $service_id");
        $success = "Layanan berhasil dihapus!";
        header("Location: kelola-layanan.php");
        exit;
    }
}

// Icon list untuk picker
$icon_list = [
    'fas fa-flag', 'fas fa-sticky-note', 'fas fa-bullhorn', 'fas fa-newspaper',
    'fas fa-box', 'fas fa-palette', 'fas fa-print', 'fas fa-paint-brush',
    'fas fa-tags', 'fas fa-shipping-fast', 'fas fa-clock', 'fas fa-crown',
    'fas fa-bolt', 'fas fa-headset', 'fas fa-award', 'fas fa-gem'
];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Layanan - King Printing</title>
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

        .services-grid {
            display: grid;
            gap: 1rem;
        }

        .service-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 1.5rem;
            display: flex;
            gap: 1.5rem;
            align-items: flex-start;
        }

        .service-icon {
            width: 60px;
            height: 60px;
            background: #9a2020;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .service-info {
            flex: 1;
        }

        .service-title {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
        }

        .service-description {
            color: #666;
            margin-bottom: 0.5rem;
            line-height: 1.5;
        }

        .service-actions {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
            align-items: center;
        }

        .service-actions span {
            font-weight: 500;
            color: #333;
        }

        .service-actions input {
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

        /* Icon Picker Styles */
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

        /* === MODAL STYLES === */
        .custom-modal {
            display: none;
            position: fixed;
            z-index: 2000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(3px);
            animation: fadeIn 0.3s;
        }

        .custom-modal-content {
            background-color: #fefefe;
            padding: 25px 35px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            border-top: 5px solid #dc3545;
            animation: slideIn 0.3s;
        }

        .confirm-icon {
            font-size: 3rem;
            color: #dc3545;
            margin-bottom: 1rem;
        }

        .custom-modal h3 {
            margin-top: 0;
            color: #333;
            font-size: 1.4rem;
            margin-bottom: 0.5rem;
        }

        .custom-modal p {
            color: #666;
            margin-bottom: 1.5rem;
        }

        .confirm-actions {
            display: flex;
            justify-content: center;
            gap: 1rem;
        }

        .btn-confirm-yes {
            background-color: #dc3545;
            color: white;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .btn-confirm-yes:hover {
            background-color: #c82333;
        }

        .btn-confirm-no {
            background-color: #e9ecef;
            color: #333;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 500;
            font-family: 'Poppins', sans-serif;
            transition: all 0.2s;
        }

        .btn-confirm-no:hover {
            background-color: #dee2e6;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes slideIn {
            from { transform: translate(-50%, -60%); opacity: 0; }
            to { transform: translate(-50%, -50%); opacity: 1; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <header class="main-header">
            <h1>Kelola Layanan</h1>
            <a href="kelola-tampilan.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali
            </a>
        </header>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Form Tambah Layanan Baru -->
        <div class="form-section">
            <h2 class="section-title">Tambah Layanan Baru</h2>
            <form method="POST">
                <div class="form-group">
                    <label>Nama Layanan</label>
                    <input type="text" name="title" class="form-control" placeholder="Spanduk & Banner" required>
                </div>
                
                <div class="form-group">
                    <label>Deskripsi Layanan</label>
                    <textarea name="description" class="form-control" rows="3" placeholder="Deskripsi singkat tentang layanan ini" required></textarea>
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

                <div class="form-group">
                    <label>Teks Tombol</label>
                    <input type="text" name="button_text" class="form-control" value="Pesan Sekarang" required>
                </div>

                <button type="submit" name="add_service" class="btn btn-success">
                    <i class="fas fa-plus"></i> Tambah Layanan
                </button>
            </form>
        </div>

        <!-- Daftar Layanan -->
        <div class="form-section">
            <h2 class="section-title">Layanan Kami (<?php echo count($services); ?>)</h2>
            
            <?php if (empty($services)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">Belum ada layanan.</p>
            <?php else: ?>
                <!-- Form untuk Update Order -->
                <form method="POST" id="orderForm">
                    <div class="services-grid">
                        <?php foreach ($services as $service): ?>
                        <div class="service-card">
                            <div class="service-icon">
                                <i class="<?php echo $service['icon']; ?>"></i>
                            </div>
                            
                            <div class="service-info">
                                <div class="service-title"><?php echo $service['title']; ?></div>
                                <div class="service-description"><?php echo $service['description']; ?></div>
                                
                                <div class="service-actions">
                                    <span>Urutan: 
                                        <input type="number" name="service_order[<?php echo $service['id']; ?>]" 
                                            value="<?php echo $service['sort_order']; ?>" min="1" style="width: 60px;">
                                    </span>
                                    
                                    <!-- Tombol Hapus dengan Modal Trigger -->
                                    <div style="display: inline;">
                                        <button type="button" class="btn btn-danger delete-btn" 
                                                data-id="<?php echo $service['id']; ?>">
                                            <i class="fas fa-trash"></i> Hapus
                                        </button>
                                    </div>
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

    <!-- Hidden form untuk delete action -->
    <form id="deleteForm" method="POST" style="display:none;">
        <input type="hidden" name="service_id" id="deleteServiceId">
        <input type="hidden" name="delete_service" value="1">
    </form>

    <!-- Modal Konfirmasi Hapus -->
    <div id="deleteModal" class="custom-modal">
        <div class="custom-modal-content">
            <div class="confirm-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3>Hapus Layanan?</h3>
            <p>Layanan ini akan dihapus secara permanen. Anda yakin ingin melanjutkan?</p>
            <div class="confirm-actions">
                <button id="confirmDelete" class="btn-confirm-yes">Ya, Hapus</button>
                <button id="cancelDelete" class="btn-confirm-no">Batal</button>
            </div>
        </div>
    </div>

    <script>
        // Icon Picker
        document.querySelectorAll('.icon-option').forEach(option => {
            option.addEventListener('click', function() {
                document.querySelectorAll('.icon-option').forEach(opt => opt.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('selected_icon').value = this.dataset.icon;
            });
        });

        // Modal Delete Logic
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.delete-btn');
            const deleteModal = document.getElementById('deleteModal');
            const confirmBtn = document.getElementById('confirmDelete');
            const cancelBtn = document.getElementById('cancelDelete');
            const deleteForm = document.getElementById('deleteForm');
            const deleteInput = document.getElementById('deleteServiceId');

            let serviceIdToDelete = null;

            deleteButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    serviceIdToDelete = this.getAttribute('data-id');
                    deleteModal.style.display = 'block';
                });
            });

            confirmBtn.addEventListener('click', function() {
                if (serviceIdToDelete) {
                    deleteInput.value = serviceIdToDelete;
                    deleteForm.submit();
                }
            });

            cancelBtn.addEventListener('click', function() {
                deleteModal.style.display = 'none';
                serviceIdToDelete = null;
            });

            window.addEventListener('click', function(e) {
                if (e.target == deleteModal) {
                    deleteModal.style.display = 'none';
                    serviceIdToDelete = null;
                }
            });
        });
    </script>
</body>
</html>