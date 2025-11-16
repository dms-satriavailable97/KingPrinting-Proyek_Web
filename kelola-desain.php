<?php
session_start();
if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    header("location: index.php");
    exit;
}
require_once 'config.php';

// Daftar produk
$produks = ['Spanduk & Banner', 'Stiker & Label', 'Baliho & Billboard', 'Brosur & Flyer', 'Kemasan & Dus', 'Produk Custom'];

// Handle upload
if ($_POST['action'] ?? '' === 'tambah') {
    $produk = $_POST['produk'];
    $caption = $_POST['caption'];
    $uploadDir = 'assets/desain/';
    $fileName = time() . '_' . basename($_FILES['gambar']['name']);
    $targetFile = $uploadDir . $fileName;

    if (move_uploaded_file($_FILES['gambar']['tmp_name'], $targetFile)) {
        $stmt = $conn->prepare("INSERT INTO desain_produk (produk, gambar, caption) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $produk, $targetFile, $caption);
        $stmt->execute();
        $stmt->close();
        $success = "Desain berhasil ditambahkan!";
    } else {
        $error = "Gagal upload gambar.";
    }
}

// Hapus
if (isset($_GET['hapus'])) {
    $id = (int)$_GET['hapus'];
    $stmt = $conn->prepare("SELECT gambar FROM desain_produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        unlink($row['gambar']); // Hapus file
    }
    $stmt->close();

    $stmt = $conn->prepare("DELETE FROM desain_produk WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $stmt->close();
    header("Location: kelola-desain.php");
    exit;
}

// Ambil semua desain
$desains = $conn->query("SELECT d.*, p.id as pid FROM desain_produk d ORDER BY d.id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Desain - King Printing</title>
    <link rel="stylesheet" href="dashboard.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        .upload-form { background: white; padding: 2rem; border-radius: 15px; margin-bottom: 2rem; }
        .upload-form input, .upload-form select, .upload-form button { margin: 0.5rem 0; width: 100%; padding: 0.8rem; border-radius: 8px; border: 1px solid #ddd; }
        .upload-form button { background: var(--brand-red); color: white; font-weight: 600; cursor: pointer; }
        .desain-list img { width: 80px; height: 80px; object-fit: cover; border-radius: 8px; }
        .action-btn.delete { background: #e74c3c; }
        .action-btn.delete:hover { background: #c0392b; }
    </style>
</head>
<body>
<div class="dashboard-container">
    <aside class="sidebar">
        <div class="sidebar-header"><i class="fas fa-crown"></i><h2>King Printing</h2></div>
        <nav class="sidebar-nav">
            <ul>
                <li><a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="pesanan.php"><i class="fas fa-inbox"></i> Pesanan</a></li>
                <li><a href="riwayat-pesanan.php"><i class="fas fa-history"></i> Riwayat</a></li>
                <li><a href="kelola-desain.php" class="active"><i class="fas fa-images"></i> Kelola Desain</a></li>
            </ul>
        </nav>
        <div class="sidebar-footer"><a href="logout.php" class="logout-btn"><i class="fas fa-sign-out-alt"></i> Logout</a></div>
    </aside>

    <main class="main-content">
        <header class="main-header">
            <div class="header-left"><h3>Kelola Contoh Desain</h3></div>
        </header>

        <?php if (isset($success)) echo "<p style='color:green; background:#d4edda; padding:1rem; border-radius:8px;'>$success</p>"; ?>
        <?php if (isset($error)) echo "<p style='color:red; background:#f8d7da; padding:1rem; border-radius:8px;'>$error</p>"; ?>

        <div class="upload-form">
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="tambah">
                <select name="produk" required>
                    <option value="">Pilih Produk</option>
                    <?php foreach ($produks as $p): ?>
                        <option value="<?= htmlspecialchars($p) ?>"><?= htmlspecialchars($p) ?></option>
                    <?php endforeach; ?>
                </select>
                <input type="text" name="caption" placeholder="Judul Desain (opsional)">
                <input type="file" name="gambar" accept="image/*" required>
                <button type="submit">Tambah Desain</button>
            </form>
        </div>

        <section class="customers-table">
            <div class="table-header"><h3>Daftar Desain</h3></div>
            <table>
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Produk</th>
                        <th>Caption</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($desains as $d): ?>
                    <tr>
                        <td><img src="<?= htmlspecialchars($d['gambar']) ?>" alt=""></td>
                        <td><?= htmlspecialchars($d['produk']) ?></td>
                        <td><?= htmlspecialchars($d['caption'] ?? '-') ?></td>
                        <td>
                            <a href="?hapus=<?= $d['id'] ?>" class="action-btn delete" onclick="return confirm('Hapus desain ini?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </section>
    </main>
</div>
</body>
</html>