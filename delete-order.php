<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Akses tidak diizinkan atau data tidak valid.'];

// Pastikan admin sudah login dan metode request adalah POST dengan ID
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && $_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id'])) {
    
    $orderId = intval($_POST['id']);
    
    if ($orderId > 0) {
        $sql = "DELETE FROM pesanan WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $orderId);

        if ($stmt->execute()) {
            // Cek apakah ada baris yang benar-benar terhapus
            if ($stmt->affected_rows > 0) {
                $response['success'] = true;
                $response['message'] = 'Pesanan berhasil dihapus.';
            } else {
                $response['message'] = 'Pesanan tidak ditemukan atau sudah dihapus.';
            }
        } else {
            $response['message'] = 'Eksekusi query gagal: ' . $stmt->error;
        }
        $stmt->close();
    }
}

$conn->close();
echo json_encode($response);
?>