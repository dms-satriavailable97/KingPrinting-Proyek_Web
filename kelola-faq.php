<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// --- PAGINATION CONFIG ---
$limit = 5; // Jumlah item per halaman

// Hitung Total Data DULU untuk validasi halaman
$sql_count = "SELECT COUNT(*) as total FROM website_items WHERE section_name = 'faq' AND item_type = 'faq'";
$result_count = $conn->query($sql_count);
$total_items = $result_count->fetch_assoc()['total'];
$total_pages = ceil($total_items / $limit);
if ($total_pages == 0) $total_pages = 1; // Minimal 1 halaman

// Ambil halaman saat ini
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// --- LOGIKA PERBAIKAN PAGINATION SETELAH HAPUS ---
// Jika halaman yang diminta lebih besar dari total halaman yang ada
if ($page > $total_pages) {
    header("Location: kelola-faq.php?page=" . $total_pages);
    exit;
}
if ($page < 1) $page = 1;

$offset = ($page - 1) * $limit;

// Get FAQ data
$faqs = [];
$sql = "SELECT * FROM website_items WHERE section_name = 'faq' AND item_type = 'faq' ORDER BY sort_order LIMIT $limit OFFSET $offset";
$result = $conn->query($sql);
if ($result) {
    while($row = $result->fetch_assoc()) {
        $faqs[] = $row;
    }
}

// Process form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Tambah FAQ
    if (isset($_POST['add_faq'])) {
        $title = $conn->real_escape_string($_POST['title']);
        $description = $conn->real_escape_string($_POST['description']);
        
        $max_order = $conn->query("SELECT MAX(sort_order) as max_order FROM website_items WHERE section_name = 'faq'")->fetch_assoc()['max_order'] ?? 0;
        $new_order = $max_order + 1;
        
        $sql = "INSERT INTO website_items (section_name, item_type, title, description, sort_order) 
                VALUES ('faq', 'faq', '$title', '$description', $new_order)";
        if ($conn->query($sql)) {
            header("Location: kelola-faq.php?page=" . $page);
            exit;
        }
    }
    
    // 2. Update Urutan
    if (isset($_POST['update_order'])) {
        foreach ($_POST['faq_order'] as $faq_id => $order) {
            $conn->query("UPDATE website_items SET sort_order = " . intval($order) . " WHERE id = " . intval($faq_id));
        }
        // Redirect dengan sinyal sukses
        header("Location: kelola-faq.php?page=" . $page . "&status=success");
        exit;
    }
    
    // 3. Hapus FAQ
    if (isset($_POST['delete_faq'])) {
        $faq_id = intval($_POST['delete_faq']);
        $conn->query("DELETE FROM website_items WHERE id = $faq_id");
        
        // Redirect kembali ke halaman saat ini (validasi halaman dilakukan di atas)
        header("Location: kelola-faq.php?page=" . $page);
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
        /* === CSS BASE === */
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Poppins', sans-serif; }
        body { background: #f5f5f5; color: #333; }
        .admin-container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        
        .main-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .main-header h1 { color: #9a2020; font-size: 1.8rem; font-weight: 600; }
        
        .form-section { background: white; border-radius: 10px; padding: 2rem; margin-bottom: 2rem; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .section-title { color: #9a2020; margin-bottom: 1.5rem; border-bottom: 2px solid #FFD700; padding-bottom: 0.5rem; font-weight: 600; }
        
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.5rem; font-weight: 500; color: #333; }
        .form-control { width: 100%; padding: 0.8rem; border: 1px solid #ddd; border-radius: 5px; font-family: 'Poppins', sans-serif; font-size: 0.9rem; }
        
        /* === BUTTON STYLES === */
        .btn { padding: 0.8rem 1.5rem; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; font-family: 'Poppins', sans-serif; font-weight: 500; font-size: 0.9rem; transition: all 0.3s; }
        .btn-sm-icon { padding: 0.5rem 0.8rem; font-size: 0.9rem; line-height: 1; border-radius: 6px; }
        
        .btn-primary { background: #9a2020; color: white; }
        .btn-primary:hover { background: #7a1a1a; }
        
        .btn-success { background: #28a745; color: white; }
        .btn-success:hover { background: #218838; }
        
        .btn-danger { background: #dc3545; color: white; }
        .btn-danger:hover { background: #c82333; }
        
        .btn-secondary { background: #6c757d; color: white; }
        .btn-secondary:hover { background: #5a6268; }

        /* === CARD LIST & PAGINATION === */
        .faq-list { display: grid; gap: 1rem; }
        .faq-card { background: #f8f9fa; border: 1px solid #dee2e6; border-radius: 8px; padding: 1.5rem; width: 100%; overflow: hidden; }
        .faq-question { font-weight: 600; color: #333; margin-bottom: 0.5rem; font-size: 1.1rem; word-break: break-word; }
        .faq-answer { color: #666; line-height: 1.5; word-break: break-word; }
        
        .faq-actions { margin-top: 1rem; display: flex; gap: 0.5rem; flex-wrap: wrap; align-items: center; }
        .faq-actions span { font-weight: 500; color: #333; }
        .faq-actions input { width: 60px; padding: 0.3rem; border: 1px solid #ddd; border-radius: 3px; text-align: center; }

        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem; padding-top: 1rem; border-top: 1px solid #eee; }
        .pagination a { display: inline-flex; align-items: center; justify-content: center; width: 40px; height: 40px; border-radius: 8px; background: white; color: #555; text-decoration: none; border: 1px solid #ddd; font-weight: 500; transition: all 0.3s ease; }
        .pagination a:hover { background: #f5f5f5; border-color: #9a2020; color: #9a2020; transform: translateY(-2px); }
        .pagination a.active { background: #9a2020; color: white; border-color: #9a2020; box-shadow: 0 4px 10px rgba(154, 32, 32, 0.3); }

        /* === CUSTOM MODAL === */
        .custom-modal { display: none; position: fixed; z-index: 9999; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0, 0, 0, 0.5); backdrop-filter: blur(3px); animation: fadeIn 0.2s ease-out; }
        .custom-modal-content { background-color: #fff; margin: 15% auto; padding: 0; border-radius: 12px; width: 90%; max-width: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); border-top: 6px solid #9a2020; animation: slideIn 0.3s ease-out; position: relative; }
        .custom-modal-header { padding: 20px 20px 10px; text-align: center; }
        .custom-modal-icon { font-size: 3rem; color: #dc3545; margin-bottom: 15px; }
        .custom-modal-title { font-size: 1.4rem; font-weight: 600; color: #333; margin: 0; }
        .custom-modal-body { padding: 10px 25px 25px; text-align: center; color: #666; font-size: 0.95rem; }
        .custom-modal-footer { padding: 15px 20px; background: #f9f9f9; border-top: 1px solid #eee; border-radius: 0 0 12px 12px; display: flex; justify-content: center; gap: 15px; }

        /* === TOAST NOTIFICATION === */
        .toast-notification {
            position: fixed; top: 20px; right: 20px; z-index: 10000;
            background: white; border-left: 5px solid #28a745;
            padding: 15px 20px; border-radius: 4px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.15);
            display: flex; align-items: center; gap: 15px;
            transform: translateX(120%); transition: transform 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }
        .toast-notification.show { transform: translateX(0); }
        .toast-icon { color: #28a745; font-size: 1.5rem; }
        .toast-content h4 { margin: 0; font-size: 1rem; color: #333; }
        .toast-content p { margin: 2px 0 0; font-size: 0.85rem; color: #666; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideIn { from { transform: translateY(-20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
    </style>
</head>
<body>
    <!-- Toast Notification Container -->
    <div class="toast-notification" id="successToast">
        <div class="toast-icon"><i class="fas fa-check-circle"></i></div>
        <div class="toast-content">
            <h4>Berhasil!</h4>
            <p>Urutan FAQ berhasil diperbarui.</p>
        </div>
    </div>

    <div class="admin-container">
        <header class="main-header">
            <h1>Kelola FAQ</h1>
            <a href="kelola-tampilan.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Kembali 
            </a>
        </header>

        <!-- Form Tambah FAQ -->
        <div class="form-section">
            <h2 class="section-title">Tambah FAQ Baru</h2>
            <form method="POST" class="scroll-preserve">
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
            <h2 class="section-title">Pertanyaan Umum (Total: <?php echo $total_items; ?>)</h2>
            
            <?php if (empty($faqs)): ?>
                <p style="text-align: center; color: #666; padding: 2rem;">Belum ada FAQ.</p>
            <?php else: ?>
                
                <form method="POST" id="orderForm" class="scroll-preserve">
                    <div class="faq-list">
                        <?php foreach ($faqs as $faq): ?>
                        <div class="faq-card">
                            <div class="faq-question"><?php echo htmlspecialchars($faq['title']); ?></div>
                            <div class="faq-answer"><?php echo htmlspecialchars($faq['description']); ?></div>
                            <div class="faq-actions">
                                <span>Urutan: 
                                    <input type="number" name="faq_order[<?php echo $faq['id']; ?>]" 
                                        value="<?php echo $faq['sort_order']; ?>" min="1" style="width: 60px;">
                                </span>
                                <div style="margin-left: auto;">
                                    <button type="button" 
                                            class="btn btn-danger btn-sm-icon delete-trigger" 
                                            data-id="<?php echo $faq['id']; ?>"
                                            title="Hapus FAQ">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div style="margin-top: 1rem; text-align: right;">
                        <!-- TOMBOL SIMPAN URUTAN -->
                        <button type="submit" name="update_order" id="btnUpdateOrder" class="btn btn-primary">
                            <i class="fas fa-save"></i> Simpan Urutan
                        </button>
                    </div>
                </form>

                <!-- PAGINATION -->
                <?php if ($total_pages > 1): ?>
                <div class="pagination">
                    <?php if ($page > 1): ?>
                        <a href="?page=<?php echo $page - 1; ?>" title="Sebelumnya"><i class="fas fa-chevron-left"></i></a>
                    <?php endif; ?>
                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                        <a href="?page=<?php echo $i; ?>" class="<?php echo ($i == $page) ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $total_pages): ?>
                        <a href="?page=<?php echo $page + 1; ?>" title="Selanjutnya"><i class="fas fa-chevron-right"></i></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

            <?php endif; ?>
        </div>
    </div>

    <!-- MODAL CONFIRM HAPUS -->
    <div id="deleteModal" class="custom-modal">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <div class="custom-modal-icon">
                    <i class="fas fa-exclamation-circle"></i>
                </div>
                <h3 class="custom-modal-title">Konfirmasi Hapus</h3>
            </div>
            <div class="custom-modal-body">
                <p>Apakah Anda yakin ingin menghapus FAQ ini?</p>
            </div>
            <div class="custom-modal-footer">
                <!-- POSISI TOMBOL DITUKAR: Hapus Kiri, Batal Kanan -->
                <button id="confirmDelete" class="btn btn-danger">Ya, Hapus</button>
                <button id="cancelDelete" class="btn btn-secondary">Batal</button>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // === 1. SCROLL PRESERVE LOGIC ===
        const scrollPos = localStorage.getItem('faqScrollPos');
        if (scrollPos) {
            window.scrollTo(0, parseInt(scrollPos));
            localStorage.removeItem('faqScrollPos');
        }
        function saveScrollPosition() {
            localStorage.setItem('faqScrollPos', window.scrollY);
        }
        document.querySelectorAll('.scroll-preserve').forEach(form => {
            form.addEventListener('submit', function() {
                saveScrollPosition();
            });
        });

        // === 2. SUCCESS NOTIFICATION LOGIC ===
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            const toast = document.getElementById('successToast');

            if(toast) {
                // Hapus parameter URL
                window.history.replaceState({}, document.title, window.location.pathname + window.location.search.replace(/[\?&]status=success/, ''));
                
                // Animasi Masuk Toast
                setTimeout(() => {
                    toast.classList.add('show');
                }, 100);

                // Animasi Keluar Toast
                setTimeout(() => {
                    toast.classList.remove('show');
                }, 4000);
            }
        }

        // === 3. DELETE MODAL LOGIC ===
        const modal = document.getElementById('deleteModal');
        const cancelBtn = document.getElementById('cancelDelete');
        const confirmBtn = document.getElementById('confirmDelete');
        const orderForm = document.getElementById('orderForm');
        let idToDelete = null;

        document.querySelectorAll('.delete-trigger').forEach(button => {
            button.addEventListener('click', function() {
                idToDelete = this.getAttribute('data-id');
                modal.style.display = 'block';
            });
        });

        cancelBtn.addEventListener('click', function() {
            modal.style.display = 'none';
            idToDelete = null;
        });

        confirmBtn.addEventListener('click', function() {
            if (idToDelete && orderForm) {
                saveScrollPosition();
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'delete_faq';
                hiddenInput.value = idToDelete;
                orderForm.appendChild(hiddenInput);
                orderForm.submit();
            }
        });

        window.addEventListener('click', function(e) {
            if (e.target == modal) {
                modal.style.display = 'none';
                idToDelete = null;
            }
        });
    });
    </script>
</body>
</html>