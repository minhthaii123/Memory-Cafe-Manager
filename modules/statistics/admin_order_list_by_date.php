<?php
session_start();
include __DIR__ . '/../../config/config.php';

// Kiểm tra ngày được chọn
$selectedDate = $_GET['date'] ?? null;
if (!$selectedDate) {
    die("Vui lòng chọn ngày để xem đơn hàng");
}

// Lấy đơn hàng theo ngày
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
    WHERE DATE(o.created_at) = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$selectedDate]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng doanh thu trong ngày
$dailyRevenue = array_sum(array_column($orders, 'total_price'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Đơn hàng ngày <?= htmlspecialchars($selectedDate) ?></title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-btn {
            padding: 5px 10px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
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
            background-color: #343a40;
            color: white;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Đơn hàng ngày <?= htmlspecialchars($selectedDate) ?></h1>
        <a href="/trangadmin.php" class="back-btn">← Quay lại báo cáo</a>
    </div>
    
    <div class="summary">
        <strong>Tổng đơn hàng:</strong> <?= count($orders) ?> |
        <strong>Tổng doanh thu:</strong> <?= number_format($dailyRevenue, 0, ',', '.') ?> ₫
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Mã đơn</th>
                <th>Người tạo</th>
                <th>Số sản phẩm</th>
                <th class="text-right">Tổng tiền</th>
                <th>Trạng thái</th>
                <th class="text-center">Thời gian</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($orders as $order): ?>
                <tr>
                    <td><?= htmlspecialchars($order['id']) ?></td>
                    <td><?= htmlspecialchars($order['creator_name']) ?></td>
                    <td><?= $order['item_count'] ?></td>
                    <td class="text-right"><?= number_format($order['total_price'], 0, ',', '.') ?> ₫</td>
                    <td><?= htmlspecialchars($order['status']) ?></td>
                    <td class="text-center"><?= date('H:i:s', strtotime($order['created_at'])) ?></td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($orders)): ?>
                <tr>
                    <td colspan="7" class="text-center">Không có đơn hàng nào trong ngày này</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>