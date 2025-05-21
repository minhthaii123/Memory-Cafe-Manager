<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
include __DIR__ . '/../../config/config.php';

// Lấy tất cả đơn hàng với thông tin người tạo
$stmt = $conn->prepare("
    SELECT 
        o.id, 
        o.total_price, 
        o.status, 
        o.created_at,
        u.fullname as creator_name,
        u.username,
        COUNT(od.id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_details od ON o.id = od.order_id
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute();
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản lý đơn hàng - Admin</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .status {
            font-weight: bold;
        }
        .status-Chờ_xử_lý { color: #e67e22; }
        .status-Đang_xử_lý { color: #3498db; }
        .status-Hoàn_thành { color: #27ae60; }
        .status-Hủy { color: #e74c3c; }
    </style>
</head>
<body>
    <h1>Danh sách đơn hàng toàn hệ thống</h1>
    
    <table class="totallistorder">
        <thead class="totallistorder">
            <tr class="totallistorder">
                <th>Mã đơn</th>
                <th>Người tạo</th>
                <th>Tài khoản</th>
                <th>Số sản phẩm</th>
                <th>Tổng tiền</th>
                <th>Trạng thái</th>
                <th>Ngày tạo</th>
                <th>Hoạt Động</th>
                
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): 
                $statusClass = 'status-' . str_replace(' ', '_', $order['status']);
            ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['creator_name']) ?></td>
                    <td><?= htmlspecialchars($order['username']) ?></td>
                    <td><?= $order['item_count'] ?></td>
                    <td><?= number_format($order['total_price'], 0, ',', '.') ?> ₫</td>
                    <td class="status <?= $statusClass ?>">
                        <?= htmlspecialchars($order['status']) ?>
                    </td>
                    <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                    <td>
                        <a href="/modules/orders/order_detail.php?id=<?= $order['id'] ?>">Xem chi tiết</a> - 
                        <a href="/modules/orders/delete.php?id=<?= $order['id'] ?>" 
                            onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?');">
                             Xóa
                        </a>


                    </td>
                    
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="8" style="text-align: center;">Chưa có đơn hàng nào trong hệ thống</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>