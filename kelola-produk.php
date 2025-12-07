<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: login.php");
    exit;
}

require_once 'config.php';

$sql_notif = "SELECT COUNT(*) as jumlah_baru FROM pesanan WHERE status = 'Tertunda'";
$result_notif = $conn->query($sql_notif);
$badge_count = $result_notif->fetch_assoc()['jumlah_baru'];

// Daftar Kategori
$kategori_list = [
    'Spanduk & Banner', 
    'Stiker & Label', 
    'Baliho & Billboard', 
    'Brosur & Flyer', 
    'Kemasan & Dus', 
    'Produk Custom'
];

$filter_kategori = isset($_GET['kategori']) ? urldecode($_GET['kategori']) : $kategori_list[0];

// === LOGIKA PROSES (CRUD) ===
$pesan = '';
$tipe_pesan = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';
    $uploadDir = 'assets/desain/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);

    // 1. TAMBAH
    if ($aksi === 'tambah') {
        $produk = $_POST['produk'];
        $caption = trim($_POST['caption']);
        
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === 0) {
            $ext = pathinfo($_FILES['gambar']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_' . uniqid() . '.' . $ext;
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
                $stmt = $conn->prepare("INSERT INTO desain_produk (produk, gambar, caption) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $produk, $targetFile, $caption);
                $stmt->execute();
                $pesan = "Desain berhasil ditambahkan!";
                $tipe_pesan = "sukses";
            } else {
                $pesan = "Gagal upload gambar.";
                $tipe_pesan = "error";
            }
        }
    }

    // 2. EDIT
    elseif ($aksi === 'edit') {
        $id = (int)$_POST['id'];
        $caption_baru = trim($_POST['caption']);
        
        if (isset($_FILES['gambar_baru']) && $_FILES['gambar_baru']['error'] === 0) {
            $ext = pathinfo($_FILES['gambar_baru']['name'], PATHINFO_EXTENSION);
            $fileName = time() . '_edit_' . uniqid() . '.' . $ext;
            $targetFile = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['gambar_baru']['tmp_name'], $targetFile)) {
                $stmt_cek = $conn->prepare("SELECT gambar FROM desain_produk WHERE id = ?");
                $stmt_cek->bind_param("i", $id);
                $stmt_cek->execute();
                $res_cek = $stmt_cek->get_result();
                if ($row = $res_cek->fetch_assoc()) {
                    if (file_exists($row['gambar'])) unlink($row['gambar']);
                }

                $stmt = $conn->prepare("UPDATE desain_produk SET gambar = ?, caption = ? WHERE id = ?");
                $stmt->bind_param("ssi", $targetFile, $caption_baru, $id);
                $stmt->execute();
                $pesan = "Gambar dan caption berhasil diperbarui!";
                $tipe_pesan = "sukses";
            }
        } else {
            $stmt = $conn->prepare("UPDATE desain_produk SET caption = ? WHERE id = ?");
            $stmt->bind_param("si", $caption_baru, $id);
            $stmt->execute();
            $pesan = "Caption berhasil diperbarui!";
            $tipe_pesan = "sukses";
        }
    }
}

// 3. HAPUS
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("SELECT gambar FROM desain_produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($row = $res->fetch_assoc()) {
        if (file_exists($row['gambar'])) unlink($row['gambar']);
    }
    
    $stmt = $conn->prepare("DELETE FROM desain_produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    
    header("Location: kelola-produk.php?kategori=" . urlencode($filter_kategori));
    exit;
}

// AMBIL DATA
$stmt = $conn->prepare("SELECT * FROM desain_produk WHERE produk = ? ORDER BY id DESC");
$stmt->bind_param("s", $filter_kategori);
$stmt->execute();
$desains = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Katalog - Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    
    <style>
        /* === 1. STYLE HALAMAN UTAMA === */
        
        .filter-section {
            background: white; padding: 20px; border-radius: 12px; margin-bottom: 25px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05); 
            border: 1px solid #eee;
            display: flex;
            align-items: center;
            gap: 16px;
            justify-content: space-between;
            flex-wrap: wrap; /* Allow wrapping on mobile */
        }
        .filter-left {
            display: flex;
            align-items: center;
            gap: 14px;
            flex: 1;
            min-width: 250px;
        }
        .filter-left label {
            font-weight: 600;
            color: #444;
            white-space: nowrap;
        }
        .filter-left select {
            padding: 10px 15px; border-radius: 8px; border: 1px solid #ddd; 
            flex: 1; font-family: 'Poppins', sans-serif; font-size: 14px;
            outline: none; transition: border-color 0.3s;
            background: #fff;
        }
        .filter-left select:focus {
            border-color: #3b82f6;
            box-shadow: 0 0 0 3px rgba(59,130,246,0.12);
        }
        .btn-add-design {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: #3b82f6;
            color: white;
            padding: 11px 18px;
            border-radius: 8px;
            border: none;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            box-shadow: 0 6px 16px rgba(59,130,246,0.3);
            transition: transform 0.2s, box-shadow 0.2s;
            text-decoration: none;
            white-space: nowrap;
        }
        .btn-add-design:hover {
            transform: translateY(-1px);
            box-shadow: 0 8px 18px rgba(59,130,246,0.35);
        }
        .btn-add-design i {
            font-size: 13px;
        }

        /* --- PERBAIKAN GRID AGAR TIDAK MENGECIL --- */
        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr)); 
            gap: 25px;
        }

        .card-item {
            background: white; border-radius: 12px; overflow: hidden;
            box-shadow: 0 4px 10px rgba(0,0,0,0.08); border: 1px solid #f0f0f0;
            display: flex; flex-direction: column;
            transition: transform 0.3s;
        }
        .card-item:hover { transform: translateY(-5px); }

        /* --- PERBAIKAN GAMBAR AGAR FULL CONTENT (UTUH) --- */
        .card-img-wrapper {
            width: 100%;
            height: 220px;
            background-color: #f8f9fa;
            display: flex;
            align-items: center;
            justify-content: center;
            border-bottom: 1px solid #eee;
            padding: 10px;
            box-sizing: border-box;
        }

        .card-img { 
            width: 100%; 
            height: 100%; 
            object-fit: contain; 
        }

        .card-body { padding: 15px; flex: 1; display: flex; flex-direction: column; }
        .card-caption { font-size: 0.95rem; font-weight: 600; margin-bottom: 15px; color: #333; text-align: center; }
        
        .btn-group { display: flex; gap: 8px; margin-top: auto; }
        .btn-action {
            flex: 1; padding: 8px; border: none; border-radius: 6px; cursor: pointer; font-size: 0.85rem;
            text-align: center; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; gap: 5px; font-weight: 500;
        }
        .btn-edit { background: #FFC107; color: #333; }
        .btn-delete { background: #ffebee; color: #c62828; }
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500; }
        .alert-sukses { background:#d4edda; color:#155724; border: 1px solid #c3e6cb; }
        .alert-error { background:#f8d7da; color:#721c24; border: 1px solid #f5c6cb; }

        /* === 2. STYLE MODAL POPUP === */
        .admin-modal {
            display: none; position: fixed; z-index: 2000; left: 0; top: 0;
            width: 100%; height: 100%; overflow: hidden;
            background-color: rgba(0, 0, 0, 0.6); backdrop-filter: blur(3px);
        }
        .admin-modal-content {
            background-color: #fff; position: absolute; top: 50%; left: 50%;
            transform: translate(-50%, -50%);
            width: 90%; max-width: 500px; padding: 30px;
            border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            font-family: 'Poppins', sans-serif !important;
        }
        .admin-modal-content h3, .admin-modal-content label, 
        .admin-modal-content input, .admin-modal-content button {
            font-family: 'Poppins', sans-serif !important;
        }
        .admin-modal-content h3 { margin-top:0; margin-bottom:20px; font-size:1.5rem; color:#333; font-weight:700; }
        .admin-modal-content label { font-weight:500; color:#555; margin-bottom:8px; display:block; }
        .admin-modal-content input[type="text"] {
            width:100%; padding:12px; border:1px solid #ddd; border-radius:8px;
            font-size:14px; margin-bottom:15px; box-sizing:border-box; outline:none;
        }
        .admin-modal-content input[type="text"]:focus { border-color:var(--brand-red, #d32f2f); }
        .admin-modal-content input[type="file"] {
            width:100%; padding:10px; background:#f9f9f9; border:1px dashed #ccc;
            border-radius:8px; margin-bottom:20px; cursor:pointer; box-sizing:border-box;
        }
        .admin-modal-content button[type="submit"] {
            width:100%; padding:12px; font-weight:600; font-size:1rem; border-radius:8px;
            background:var(--brand-red, #d32f2f); color:white; border:none; cursor:pointer;
        }
        .close-modal {
            position:absolute; right:20px; top:15px; font-size:24px; color:#aaa; cursor:pointer;
        }
        .close-modal:hover { color:#d32f2f; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar Overlay for Mobile -->
        <div class="sidebar-overlay" id="sidebarOverlay"></div>

        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header"><i class="fas fa-crown"></i><h2>King Printing</h2></div>
            <nav class="sidebar-nav">
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                    <li>
                        <a href="pesanan.php" class="<?php echo basename($_SERVER['PHP_SELF']) == 'pesanan.php' ? 'active' : ''; ?>">
                            <div style="display:flex; align-items:center; gap:10px;">
                                <i class="fas fa-inbox"></i> 
                                Pesanan
                            </div>
                            
                            <?php if ($badge_count > 0): ?>
                                <span class="notification-badge"><?php echo $badge_count; ?></span>
                            <?php endif; ?>
                        </a>
                    </li>
                    <li><a href="riwayat-pesanan.php"><i class="fas fa-history"></i> Riwayat Pesanan</a></li>
                    <li><a href="kelola-produk.php" class="active"><i class="fas fa-box-open"></i> Kelola Produk</a></li>
                    <li><a href="kelola-tampilan.php"><i class="fas fa-palette"></i> Kelola Tampilan</a></li>
                    <li><a href="kelola-akun.php"><i class="fas fa-users-cog"></i> Kelola Akun</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-left" style="display:flex; align-items:center;">
                    <i class="fas fa-bars mobile-header-toggle" id="mobileMenuToggle"></i>
                    <h3>Kelola Katalog</h3>
                </div>
            </header>

            <?php if ($pesan): ?>
                <div class="alert alert-<?= $tipe_pesan ?>"><?= $pesan ?></div>
            <?php endif; ?>

            <div class="filter-section">
                <div class="filter-left">
                    <label>Kategori Produk:</label>
                    <select onchange="window.location.href='kelola-produk.php?kategori=' + encodeURIComponent(this.value)">
                        <?php foreach ($kategori_list as $kat): ?>
                            <option value="<?= $kat ?>" <?= $filter_kategori == $kat ? 'selected' : '' ?>>
                                <?= $kat ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button class="btn-add-design" onclick="document.getElementById('modalTambah').style.display='block'">
                    <i class="fas fa-plus"></i> Tambah Desain
                </button>
            </div>

            <div class="grid-container">
                <?php if (count($desains) > 0): ?>
                    <?php foreach ($desains as $d): ?>
                        <div class="card-item">
                            <div class="card-img-wrapper">
                                <img src="<?= htmlspecialchars($d['gambar']) ?>" class="card-img" alt="Foto">
                            </div>
                            
                            <div class="card-body">
                                <div class="card-caption"><?= htmlspecialchars($d['caption'] ?: '(Tanpa Judul)') ?></div>
                                <div class="btn-group">
                                    <button class="btn-action btn-edit" 
                                            onclick="bukaModalEdit(<?= $d['id'] ?>, '<?= htmlspecialchars(addslashes($d['caption'])) ?>')">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <a href="?kategori=<?= urlencode($filter_kategori) ?>&hapus=<?= $d['id'] ?>" 
                                       class="btn-action btn-delete" 
                                       onclick="return confirm('Hapus permanen?')">
                                       <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="grid-column: 1/-1; text-align:center; padding:50px; color:#999; background: #fff; border-radius: 12px; border: 1px dashed #ddd;">
                        <i class="fas fa-camera" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i>
                        <p>Belum ada foto di kategori <strong><?= htmlspecialchars($filter_kategori) ?></strong>.</p>
                        <p style="font-size: 0.9rem;"> Gunakan "Tambah Desain" untuk menambah.</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <div id="modalTambah" class="admin-modal">
        <div class="admin-modal-content">
            <span onclick="document.getElementById('modalTambah').style.display='none'" class="close-modal">&times;</span>
            <h3>Tambah Foto Baru</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="tambah">
                <input type="hidden" name="produk" value="<?= htmlspecialchars($filter_kategori) ?>">
                <p style="margin-bottom:15px; font-size:0.9rem; color:#666;">Kategori: <strong><?= htmlspecialchars($filter_kategori) ?></strong></p>

                <label>Judul / Caption</label>
                <input type="text" name="caption" placeholder="Contoh: Banner Warung (Opsional)">

                <label>Upload Gambar</label>
                <input type="file" name="gambar" accept="image/*" required>

                <button type="submit">Simpan Desain</button>
            </form>
        </div>
    </div>

    <div id="modalEdit" class="admin-modal">
        <div class="admin-modal-content">
            <span onclick="document.getElementById('modalEdit').style.display='none'" class="close-modal">&times;</span>
            <h3>Edit Produk</h3>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="aksi" value="edit">
                <input type="hidden" name="id" id="edit_id">
                
                <label>Judul / Caption</label>
                <input type="text" name="caption" id="edit_caption">

                <label>Ganti Gambar (Opsional)</label>
                <input type="file" name="gambar_baru" accept="image/*">
                
                <button type="submit" style="background: linear-gradient(to right, #FFC107, #FF9800); color: #333;">Update</button>
            </form>
        </div>
    </div>

    <script>
        function bukaModalEdit(id, caption) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_caption').value = caption;
            document.getElementById('modalEdit').style.display = 'block';
        }
        window.onclick = function(event) {
            if (event.target.classList.contains('admin-modal')) {
                event.target.style.display = "none";
            }
        }
        
        // --- Sidebar Logic ---
        const mobileMenuToggle = document.getElementById('mobileMenuToggle');
        const sidebar = document.getElementById('sidebar');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        if(mobileMenuToggle && sidebar && sidebarOverlay) {
            mobileMenuToggle.addEventListener('click', function() {
                sidebar.classList.toggle('active');
                sidebarOverlay.classList.toggle('active');
            });

            sidebarOverlay.addEventListener('click', function() {
                sidebar.classList.remove('active');
                sidebarOverlay.classList.remove('active');
            });
        }
    </script>
</body>
</html>