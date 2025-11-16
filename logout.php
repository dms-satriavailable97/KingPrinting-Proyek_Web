<?php
// Selalu mulai session di awal
session_start();

// Hapus semua data dari array $_SESSION
$_SESSION = array();

// Hancurkan sesi yang sedang berjalan
session_destroy();

// Arahkan (redirect) user kembali ke halaman utama
header("location: index.php");
exit;
?>