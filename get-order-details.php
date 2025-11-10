<?php
require_once 'config.php';
session_start();
header('Content-Type: application/json');

$response = ['success' => false, 'message' => 'Akses tidak valid.'];

if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true && isset($_GET['id'])) {
    $orderId = intval($_GET['id']);
    
    $sql = "SELECT * FROM pesanan WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $order = $result->fetch_assoc();
        
        // Format beberapa data untuk tampilan
        $order['id_formatted'] = "KP" . str_pad($order['id'], 3, '0', STR_PAD_LEFT);
        $order['tanggal_masuk'] = date('d M Y, H:i', strtotime($order['tanggal_masuk']));
        
        // Menggunakan htmlspecialchars untuk keamanan
        foreach($order as $key => $value) {
            $order[$key] = htmlspecialchars($value);
        }

        $response['success'] = true;
        $response['order'] = $order;
        unset($response['message']);

    } else {
        $response['message'] = 'Pesanan tidak ditemukan.';
    }
    $stmt->close();
}

$conn->close();
echo json_encode($response);
?>