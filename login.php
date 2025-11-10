<?php
// Memulai session PHP
session_start();

// Cek apakah request yang masuk adalah metode POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    
    // Username dan password admin yang sudah ditentukan (hardcoded)
    $admin_username = "admin";
    $admin_password = "admin";

    // Mengambil data username dan password dari form login
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Memeriksa apakah username dan password yang diinput sesuai
    if ($username === $admin_username && $password === $admin_password) {
        // Jika sesuai, buat session untuk menandai bahwa user sudah login
        $_SESSION['loggedin'] = true;
        $_SESSION['username'] = $username;

        // Arahkan (redirect) user ke halaman dashboard
        header("location: dashboard.php");
        exit;
    } else {
        // Jika tidak sesuai, kembalikan user ke halaman utama
        // Anda bisa menambahkan parameter error di URL untuk menampilkan pesan
        header("location: index.html?error=loginfailed");
        exit;
    }
} else {
    // Jika file ini diakses langsung (bukan melalui POST), arahkan ke halaman utama
    header("location: index.html");
    exit;
}
?>