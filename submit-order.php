<?php
require_once 'config.php';
header('Content-Type: application/json');

$response = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $produk = isset($_POST['produk']) ? $conn->real_escape_string($_POST['produk']) : '';
    $ukuran = isset($_POST['ukuran']) ? $conn->real_escape_string($_POST['ukuran']) : '';
    $bahan = isset($_POST['bahan']) ? $conn->real_escape_string($_POST['bahan']) : '';
    $jumlah = isset($_POST['jumlah']) ? intval($_POST['jumlah']) : 0;
    $catatan = isset($_POST['catatan']) ? $conn->real_escape_string($_POST['catatan']) : '';
    $nama = isset($_POST['nama']) ? $conn->real_escape_string($_POST['nama']) : '';
    $telepon = isset($_POST['telepon']) ? $conn->real_escape_string($_POST['telepon']) : '';
    $status = 'Tertunda'; // Diubah dari 'Baru'

    if (empty($nama) || empty($telepon) || empty($produk)) {
        $response['success'] = false;
        $response['message'] = 'Nama, Telepon, dan Produk tidak boleh kosong!';
    } else {
        $sql = "INSERT INTO pesanan (nama_pemesan, telepon, produk, ukuran, bahan, jumlah, catatan, status) 
                VALUES ('$nama', '$telepon', '$produk', '$ukuran', '$bahan', $jumlah, '$catatan', '$status')";

        if ($conn->query($sql) === TRUE) {
            $response['success'] = true;
            $response['message'] = 'Pesanan berhasil dikirim!';
        } else {
            $response['success'] = false;
            $response['message'] = 'Gagal menyimpan ke database: ' . $conn->error;
        }
    }
} else {
    $response['success'] = false;
    $response['message'] = 'Metode tidak diizinkan.';
}

$conn->close();
echo json_encode($response);
?>