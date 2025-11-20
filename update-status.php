<?php
require_once 'config.php';
session_start();

header('Content-Type: application/json');
$response = ['success' => false, 'message' => 'Akses tidak diizinkan.'];

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['status'])) {
        $orderId = intval($_POST['id']);
        $newStatus = $conn->real_escape_string($_POST['status']);
        
        // Status yang diizinkan untuk diubah
        $allowedNewStatus = ['Proses', 'Selesai', 'Tertunda'];

        if (in_array($newStatus, $allowedNewStatus)) {
            $sql = "";
            // Tentukan query UPDATE berdasarkan status baru yang diminta
            if ($newStatus === 'Proses') {
                // Hanya bisa dari Tertunda
                $sql = "UPDATE pesanan SET status = ? WHERE id = ? AND status = 'Tertunda'";
            } elseif ($newStatus === 'Selesai') {
                // Bisa dari Tertunda atau Proses
                $sql = "UPDATE pesanan SET status = ? WHERE id = ? AND status IN ('Tertunda', 'Proses')";
            } elseif ($newStatus === 'Tertunda') {
                // Hanya bisa dari Proses
                $sql = "UPDATE pesanan SET status = ? WHERE id = ? AND status = 'Proses'";
            }
            
            if ($sql) {
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("si", $newStatus, $orderId);

                if ($stmt->execute()) {
                    if ($stmt->affected_rows > 0) {
                        $response['success'] = true;
                        $response['message'] = 'Status pesanan berhasil diperbarui menjadi ' . $newStatus . '.';
                        $response['new_status'] = $newStatus;
                    } else {
                        $response['message'] = 'Perubahan status tidak diizinkan atau pesanan tidak ditemukan.';
                        $response['success'] = false;
                    }
                } else {
                    $response['message'] = 'Gagal memperbarui status: ' . $stmt->error;
                }
                $stmt->close();
            } else {
                $response['message'] = 'Alur status tidak valid.';
            }
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