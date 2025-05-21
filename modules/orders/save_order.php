<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Chưa đăng nhập']);
    exit;
}

include __DIR__ . '/../../config/config.php';

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || !isset($data['orders']) || !is_array($data['orders'])) {
    echo json_encode(['success' => false, 'message' => 'Dữ liệu không hợp lệ']);
    exit;
}

$orders = $data['orders'];
$totalPrice = floatval($data['total_price'] ?? 0);

// TODO: Bạn có thể thêm validate dữ liệu ở đây

try {
    $conn->beginTransaction();

    // Giả sử bạn có bảng orders lưu đơn hàng chung
    $stmtOrder = $conn->prepare("INSERT INTO orders (user_id, total_price, created_at) VALUES (?, ?, NOW())");
    $stmtOrder->execute([$_SESSION['user_id'], $totalPrice]);
    $orderId = $conn->lastInsertId();

    // Lưu chi tiết đơn hàng
    $stmtDetail = $conn->prepare("INSERT INTO order_details (order_id, product_id, quantity, unit_price) VALUES (?, ?, ?, ?)");
    foreach ($orders as $item) {
        $stmtDetail->execute([$orderId, $item['product_id'], $item['quantity'], $item['unit_price']]);
    }

    $conn->commit();
    echo json_encode(['success' => true]);
} catch (PDOException $e) {
    $conn->rollBack();
    echo json_encode(['success' => false, 'message' => 'Lỗi cơ sở dữ liệu: ' . $e->getMessage()]);
}
