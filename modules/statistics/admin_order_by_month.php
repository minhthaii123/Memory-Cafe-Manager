<?php
session_start();
include __DIR__ . '/../../config/config.php';

// Kiểm tra tháng được chọn
$selectedMonth = $_GET['month'] ?? null;
if (!$selectedMonth) {
    die("Vui lòng chọn tháng để xem đơn hàng");
}

// Lấy đơn hàng theo tháng
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
    WHERE DATE_FORMAT(o.created_at, '%Y-%m') = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$selectedMonth]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng doanh thu trong tháng
$monthlyRevenue = array_sum(array_column($orders, 'total_price'));

// Định dạng tên tháng
$monthName = date('m/Y', strtotime($selectedMonth.'-01'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/admin_order_by_month.css">
    <title>Đơn hàng tháng <?= htmlspecialchars($monthName) ?></title>
</head>
<body>
    <div class="header">
        <h1>Đơn hàng tháng <?= htmlspecialchars($monthName) ?></h1>
        <a href="/trangadmin.php" class="back-btn">← Quay lại báo cáo</a>
    </div>
    
    <div class="summary">
        <strong>Tổng đơn hàng:</strong> <?= count($orders) ?> |
        <strong>Tổng doanh thu:</strong> <?= number_format($monthlyRevenue, 0, ',', '.') ?> ₫
    </div>
    
    <?php
    // Nhóm đơn hàng theo ngày
    $ordersByDay = [];
    foreach ($orders as $order) {
        $day = date('Y-m-d', strtotime($order['created_at']));
        $ordersByDay[$day][] = $order;
    }
    
    foreach ($ordersByDay as $day => $dailyOrders):
        $dayName = date('d/m/Y', strtotime($day));
        $dailyTotal = array_sum(array_column($dailyOrders, 'total_price'));
    ?>
        <div class="day-header">
            Ngày <?= $dayName ?> - 
            <?= count($dailyOrders) ?> đơn - 
            Tổng: <?= number_format($dailyTotal, 0, ',', '.') ?> ₫
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
                <?php foreach ($dailyOrders as $order): ?>
                    <tr>
                        <td><?= htmlspecialchars($order['id']) ?></td>
                        <td><?= htmlspecialchars($order['creator_name']) ?></td>
                        <td><?= $order['item_count'] ?></td>
                        <td class="text-right"><?= number_format($order['total_price'], 0, ',', '.') ?> ₫</td>
                        <td><?= htmlspecialchars($order['status']) ?></td>
                        <td class="text-center"><?= date('H:i:s', strtotime($order['created_at'])) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endforeach; ?>
    
    <?php if (empty($ordersByDay)): ?>
        <div class="text-center">Không có đơn hàng nào trong tháng này</div>
    <?php endif; ?>
</body>
</html>