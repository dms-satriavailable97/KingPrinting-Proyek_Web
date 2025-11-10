<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Akses tidak diizinkan.'];

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['status'])) {
        $orderId = intval($_POST['id']);
        $newStatus = $conn->real_escape_string($_POST['status']);
        
        // Hanya status 'Selesai' yang valid untuk diubah dari halaman pesanan
        if ($newStatus === 'Selesai') {
            $sql = "UPDATE pesanan SET status = ? WHERE id = ? AND status = 'Tertunda'";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $newStatus, $orderId);

            if ($stmt->execute()) {
                if ($stmt->affected_rows > 0) {
                    $response['success'] = true;
                    $response['message'] = 'Pesanan ditandai sebagai Selesai.';
                } else {
                    $response['message'] = 'Pesanan tidak ditemukan atau status sudah berubah.';
                    $response['success'] = false; // Eksplisit
                }
            } else {
                $response['message'] = 'Gagal memperbarui status: ' . $stmt->error;
            }
            $stmt->close();
        } else {
            $response['message'] = 'Status tujuan tidak valid.';
        }
    } else {
        $response['message'] = 'Data tidak lengkap.';
    }
}

$conn->close();
echo json_encode($response);
?>