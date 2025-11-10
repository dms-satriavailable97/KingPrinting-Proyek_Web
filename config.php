<?php
$DB_HOST = 'localhost';
$DB_USERNAME = 'root';
$DB_PASSWORD = '';
$DB_NAME = 'king_printing_db';

$conn = new mysqli($DB_HOST, $DB_USERNAME, $DB_PASSWORD, $DB_NAME);

if ($conn->connect_error) {
    die("Koneksi ke database gagal: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>