<?php
session_start();
include __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo "Vui lòng đăng nhập.";
    exit;
}

$orderId = $_GET['id'] ?? null;
if (!$orderId) {
    echo "Thiếu mã đơn hàng.";
    exit;
}

// Kiểm tra quyền truy cập đơn hàng
$check = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$check->execute([$orderId, $_SESSION['user_id']]);
if (!$check->fetch()) {
    echo "Không tìm thấy đơn hàng.";
    exit;
}

// Lấy chi tiết
$stmt = $conn->prepare("
    SELECT od.*, p.product_name 
    FROM order_details od
    JOIN products p ON od.product_id = p.id
    WHERE od.order_id = ?
");
$stmt->execute([$orderId]);
$details = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<h2>Chi tiết đơn hàng #<?= $orderId ?></h2>
<table border="1" cellpadding="8">
    <tr>
        <th>Tên sản phẩm</th>
        <th>Số lượng</th>
        <th>Đơn giá</th>
        <th>Thành tiền</th>
    </tr>
    <?php foreach ($details as $d): ?>
        <tr>
            <td><?= htmlspecialchars($d['product_name']) ?></td>
            <td><?= $d['quantity'] ?></td>
            <td><?= number_format($d['unit_price'], 0, ',', '.') ?> ₫</td>
            <td><?= number_format($d['quantity'] * $d['unit_price'], 0, ',', '.') ?> ₫</td>
        </tr>
    <?php endforeach; ?>
</table>

<a href="/trangadmin.php">← Quay lại danh Chinhs</a>
