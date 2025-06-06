<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
include __DIR__ . '/../../config/config.php';

// Lấy đơn hàng có trạng thái "Hoàn thành"
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
    WHERE o.status = 'Hủy'
    GROUP BY o.id, o.total_price, o.status, o.created_at, u.fullname, u.username
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
            color: #27ae60; /* Màu xanh cho trạng thái hoàn thành */
        }

        .btn-delete {
            background-color: #e74c3c;
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .btn-delete:hover {
            background-color: #c0392b;
        }

        .btn-xem {
            background-color: rgb(40, 215, 13);
            color: white;
            padding: 6px 12px;
            text-decoration: none;
            border-radius: 4px;
            display: inline-block;
        }
        .btn-xem:hover {
            background-color: rgb(14, 210, 73);
        }

        .order-status {
            background-color: #27ae60;
            color: white;
            padding: 5px 10px;
            border-radius: 4px;
            display: inline-block;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <h1>Danh sách đơn hàng toàn hệ thống</h1>
    
    <div class="order-status">
        Đang xem: Đơn hàng đã hủy
    </div>

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
            <?php if (!empty($orders)): ?>
                <?php foreach ($orders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['creator_name']) ?></td>
                        <td><?= htmlspecialchars($order['username']) ?></td>
                        <td><?= $order['item_count'] ?></td>
                        <td><?= number_format($order['total_price'], 0, ',', '.') ?> ₫</td>
                        <td class="status">
                            <?= htmlspecialchars($order['status']) ?>
                        </td>
                        <td><?= date('d/m/Y H:i', strtotime($order['created_at'])) ?></td>
                        <td>
                            <a href="/modules/orders/delete.php?id=<?= $order['id'] ?>" 
                               onclick="return confirm('Bạn có chắc chắn muốn xóa đơn hàng này không?');" 
                               class="btn-delete">Xóa</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" style="text-align: center;">Chưa có đơn hàng hoàn thành nào trong hệ thống</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>