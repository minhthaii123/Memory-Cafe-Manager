<?php
session_start();
include __DIR__ . '/../../config/config.php';

// Kiểm tra ngày được chọn
$selectedDate = $_GET['date'] ?? null;
if (!$selectedDate) {
    die("Vui lòng chọn ngày để xem đơn hàng");
}

// Trước tiên, lấy thống kê tổng hợp từ staticday để đối chiếu
$stmtTotal = $conn->prepare("
    SELECT 
        COUNT(o.id) as total_orders,
        SUM(o.total_price) as total_revenue
    FROM orders o
    WHERE DATE(o.created_at) = ?
    AND o.status = 'Hoàn thành'
");
$stmtTotal->execute([$selectedDate]);
$totals = $stmtTotal->fetch(PDO::FETCH_ASSOC);

// Lấy chi tiết đơn hàng hoàn thành theo ngày
$stmt = $conn->prepare("
    SELECT 
        o.id, 
        o.total_price, 
        o.created_at,
        o.status,
        u.fullname as creator_name,
        u.username,
        COUNT(od.id) as item_count
    FROM orders o
    JOIN users u ON o.user_id = u.id
    LEFT JOIN order_details od ON o.id = od.order_id
    WHERE DATE(o.created_at) = ?
    AND o.status = 'Hoàn thành'
    GROUP BY o.id, o.total_price, o.created_at, o.status, u.fullname, u.username
    ORDER BY o.created_at DESC
");
$stmt->execute([$selectedDate]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng doanh thu từ chi tiết đơn hàng để kiểm tra
$detailRevenue = array_sum(array_column($orders, 'total_price'));

// Nếu có sự khác biệt giữa tổng hợp và chi tiết, ghi log để kiểm tra
if ($totals['total_revenue'] != $detailRevenue || $totals['total_orders'] != count($orders)) {
    error_log("Phát hiện sự không khớp dữ liệu cho ngày $selectedDate:");
    error_log("Tổng hợp: " . $totals['total_orders'] . " đơn, " . $totals['total_revenue'] . " đồng");
    error_log("Chi tiết: " . count($orders) . " đơn, " . $detailRevenue . " đồng");
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/admin_order_list_by_date.css">
    <title>Đơn hàng hoàn thành - Ngày <?= htmlspecialchars($selectedDate) ?></title>
</head>
<body>
    <div class="header">
        <h1>Đơn hàng hoàn thành - Ngày <?= htmlspecialchars($selectedDate) ?></h1>
        <a href="/trangadmin.php" class="back-btn">← Quay lại báo cáo</a>
    </div>
    
    <div class="summary">
        <strong>Tổng đơn hàng hoàn thành:</strong> <?= $totals['total_orders'] ?> |
        <strong>Tổng doanh thu:</strong> <?= number_format($totals['total_revenue'], 0, ',', '.') ?> ₫
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
                    <td colspan="6" class="text-center">Không có đơn hàng hoàn thành nào trong ngày này</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>