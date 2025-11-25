<?php
session_start();
$_SESSION = array();
session_destroy();

// Redirect kembali ke halaman LOGIN ADMIN, bukan index
header("location: login.php");
exit;
?>