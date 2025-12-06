<?php
session_start();
require_once 'config.php';

$sql_notif = "SELECT COUNT(*) as jumlah_baru FROM pesanan WHERE status = 'Tertunda'";
$result_notif = $conn->query($sql_notif);
$badge_count = $result_notif->fetch_assoc()['jumlah_baru'];

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}

// === LOGIKA PROCESS FORM (HANYA EDIT) ===
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Edit Admin (Username & Password + Konfirmasi)
    if (isset($_POST['update_admin'])) {
        $id = intval($_POST['id']);
        $new_username = $conn->real_escape_string(trim($_POST['username']));
        $old_password = $_POST['old_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_new_password']; // Ambil input konfirmasi

        // Ambil password hash lama di DB untuk verifikasi
        $stmt = $conn->prepare("SELECT password FROM admins WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $stmt->bind_result($db_password_hash);
        $stmt->fetch();
        $stmt->close();

        // Verifikasi Password Lama (Wajib)
        if (password_verify($old_password, $db_password_hash)) {
            // Cek Username Duplikat
            $check = $conn->query("SELECT id FROM admins WHERE username = '$new_username' AND id != $id");
            if ($check->num_rows > 0) {
                $error = "Username '$new_username' sudah digunakan admin lain!";
            } else {
                // Logika Update
                if (!empty($new_password)) {
                    // Jika password baru diisi, CEK KONFIRMASI DULU
                    if ($new_password === $confirm_password) {
                        $new_pass_hash = password_hash($new_password, PASSWORD_DEFAULT);
                        $sql = "UPDATE admins SET username = '$new_username', password = '$new_pass_hash' WHERE id = $id";
                    } else {
                        // Jika password baru dan konfirmasi BEDA
                        $error = "Konfirmasi password baru tidak cocok!";
                        goto skip_update;
                    }
                } else {
                    // Jika password baru kosong, update username SAJA
                    $sql = "UPDATE admins SET username = '$new_username' WHERE id = $id";
                }

                // Eksekusi Query
                if (isset($sql) && $conn->query($sql)) {
                    if ($id == $_SESSION['id']) {
                        $_SESSION['username'] = $new_username;
                    }
                    header("Location: kelola-akun.php?status=success_edit");
                    exit;
                }
            }
        } else {
            $error = "Password lama salah! Perubahan dibatalkan.";
        }
        skip_update:
    }
}

// Ambil semua data admin
$admins = [];
$result = $conn->query("SELECT * FROM admins ORDER BY created_at DESC");
while($row = $result->fetch_assoc()) {
    $admins[] = $row;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Akun - King Printing</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="dashboard.css">
    <style>
        /* FIX CSS: Box Sizing agar padding tidak membuat lebar berlebih */
        * { box-sizing: border-box; }

        /* CSS Khusus Halaman Ini */
        .admin-card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .admin-card {
            background: white;
            border-radius: 12px;
            padding: 20px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.05);
            border: 1px solid #eee;
            transition: transform 0.2s;
            display: flex;
            flex-direction: column;
        }
        
        .admin-card:hover { transform: translateY(-5px); border-color: #9a2020; }

        .admin-avatar {
            width: 50px;
            height: 50px;
            background: #ffebee;
            color: #9a2020;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-bottom: 15px;
        }

        .admin-info h4 { margin: 0 0 5px 0; color: #333; font-size: 1.1rem; }
        .admin-info p { margin: 0; color: #888; font-size: 0.85rem; }

        .badge-me {
            background: #e3f2fd;
            color: #1565c0;
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 0.75rem;
            margin-left: 5px;
            vertical-align: middle;
        }

        .card-actions {
            margin-top: auto;
            padding-top: 15px;
            border-top: 1px solid #f5f5f5;
            display: flex;
            justify-content: flex-end;
            gap: 10px;
        }

        .btn-icon {
            border: none;
            background: none;
            cursor: pointer;
            font-size: 0.95rem;
            padding: 5px;
            transition: color 0.2s;
        }

        .btn-edit { color: #fbc02d; }
        .btn-edit:hover { color: #f9a825; }

        /* Modal Styles */
        .custom-modal { display: none; position: fixed; z-index: 2000; left: 0; top: 0; width: 100%; height: 100%; background-color: rgba(0,0,0,0.5); backdrop-filter: blur(3px); }
        .custom-modal-content {
            background: white;
            width: 90%;
            max-width: 400px;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.2);
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            margin: 0;
            animation: slideDown 0.3s;
        }
        @keyframes slideDown { from {opacity: 0; transform: translate(-50%, -70%);} to {opacity: 1; transform: translate(-50%, -50%);} }
        
        .close-modal { position: absolute; right: 20px; top: 20px; cursor: pointer; color: #aaa; font-size: 1.5rem; }
        .modal-title { margin: 0 0 20px 0; color: #9a2020; font-size: 1.4rem; border-bottom: 2px solid #f5f5f5; padding-bottom: 10px; }
        
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 500; color: #555; }
        .form-control { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px; font-family: 'Poppins'; }
        .btn-submit { width: 100%; padding: 12px; background: #9a2020; color: white; border: none; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-submit:hover { background: #7a1a1a; }

        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
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
                    <li><a href="kelola-produk.php"><i class="fas fa-box-open"></i> Kelola Produk</a></li>                  
                    <li><a href="kelola-tampilan.php"><i class="fas fa-palette"></i> Kelola Tampilan</a></li>
                    <li><a href="kelola-akun.php" class="active"><i class="fas fa-users-cog"></i> Kelola Akun</a></li>
                </ul>
            </nav>
            <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
        </aside>

        <main class="main-content">
            <header class="main-header">
                <div class="header-left"><h3>Kelola Akun</h3></div>
            </header>

            <!-- Notifikasi -->
            <?php if (isset($_GET['status']) && $_GET['status'] == 'success_edit'): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> Akun berhasil diperbarui!</div>
            <?php endif; ?>
            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>

            <!-- Daftar Admin (Grid) -->
            <div class="admin-card-grid">
                <?php foreach($admins as $admin): ?>
                <div class="admin-card">
                    <div style="display:flex; gap:15px; align-items:center;">
                        <div class="admin-avatar">
                            <i class="fas fa-user-shield"></i>
                        </div>
                        <div class="admin-info">
                            <h4>
                                <?php echo htmlspecialchars($admin['username']); ?>
                                <?php if($admin['id'] == $_SESSION['id']): ?>
                                    <span class="badge-me">Saya</span>
                                <?php endif; ?>
                            </h4>
                            <p>Dibuat: <?php echo date('d M Y', strtotime($admin['created_at'])); ?></p>
                        </div>
                    </div>
                    
                    <div class="card-actions">
                        <button class="btn-icon btn-edit" onclick="openEditModal(<?php echo $admin['id']; ?>, '<?php echo $admin['username']; ?>')" title="Edit Akun">
                            <i class="fas fa-edit"></i>
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </main>
    </div>

    <!-- Modal Edit Akun -->
    <div id="editAdminModal" class="custom-modal">
        <div class="custom-modal-content">
            <span class="close-modal" onclick="this.parentElement.parentElement.style.display='none'">&times;</span>
            <h3 class="modal-title">Edit Akun</h3>
            
            <form method="POST">
                <input type="hidden" name="id" id="editId">
                
                <!-- Username -->
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="editUsernameInput" class="form-control" required>
                </div>

                <hr style="margin: 15px 0; border:0; border-top:1px dashed #ddd;">

                <!-- Password Lama (Wajib) -->
                <div class="form-group">
                    <label style="color:#9a2020;">Password Lama (Wajib)</label>
                    <input type="password" name="old_password" class="form-control" required placeholder="Masukkan password lama untuk verifikasi">
                </div>

                <!-- Password Baru (Opsional) -->
                <div class="form-group">
                    <label>Password Baru (Opsional)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="Kosongkan jika tidak diganti">
                </div>

                <!-- Konfirmasi Password Baru -->
                <div class="form-group">
                    <label>Ulangi Password Baru</label>
                    <input type="password" name="confirm_new_password" class="form-control" placeholder="Ketik ulang password baru">
                </div>

                <button type="submit" name="update_admin" class="btn-submit">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <script>
        function openEditModal(id, username) {
            document.getElementById('editId').value = id;
            document.getElementById('editUsernameInput').value = username;
            document.getElementById('editAdminModal').style.display = 'block';
        }

        window.onclick = function(event) {
            if (event.target.classList.contains('custom-modal')) {
                event.target.style.display = "none";
            }
        }
    </script>
</body>
</html>