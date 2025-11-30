<?php
require_once 'config.php';

// --- KONFIGURASI ---
$username_target = 'admin';    // Username yang ingin direset/dibuat
$password_baru   = 'admin';    // Password baru
// -------------------

// 1. Enkripsi password baru
$password_hash = password_hash($password_baru, PASSWORD_DEFAULT);

// 2. Cek apakah user sudah ada?
$check = $conn->query("SELECT id FROM admins WHERE username = '$username_target'");

if ($check->num_rows > 0) {
    // A. Jika user ADA, update passwordnya
    $sql = "UPDATE admins SET password = '$password_hash' WHERE username = '$username_target'";
    if ($conn->query($sql)) {
        echo "<h1>BERHASIL UPDATE!</h1>";
        echo "Password untuk user <b>$username_target</b> berhasil diubah menjadi: <b>$password_baru</b>";
    } else {
        echo "Gagal update: " . $conn->error;
    }
} else {
    // B. Jika user TIDAK ADA, buat user baru
    $sql = "INSERT INTO admins (username, password) VALUES ('$username_target', '$password_hash')";
    if ($conn->query($sql)) {
        echo "<h1>BERHASIL DIBUAT!</h1>";
        echo "User baru <b>$username_target</b> telah dibuat dengan password: <b>$password_baru</b>";
    } else {
        echo "Gagal membuat user: " . $conn->error;
    }
}

echo "<br><br><a href='login.php'>Ke Halaman Login</a>";
echo "<br><br><strong style='color:red'>PENTING: Segera hapus file reset_password.php ini setelah berhasil login!</strong>";
?>