<?php
session_start();
include __DIR__ . '/../../config/config.php';

// Truy vấn doanh thu theo ngày
$stmt = $conn->prepare("
    SELECT 
        DATE(o.created_at) as order_date,
        COUNT(o.id) as total_orders,
        SUM(o.total_price) as total_revenue,
        AVG(o.total_price) as avg_order_value
    FROM orders o
    GROUP BY DATE(o.created_at)
    ORDER BY order_date DESC
");
$stmt->execute();
$revenueData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng doanh thu
$totalRevenue = array_sum(array_column($revenueData, 'total_revenue'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/staticday.css">
    <title>Báo cáo doanh thu theo ngày</title>
</head>
<body>
    <h1>Báo cáo doanh thu theo ngày</h1>
    
    <div class="summary">
        Tổng doanh thu: <strong><?= number_format($totalRevenue, 0, ',', '.') ?> ₫</strong> | 
        Tổng số ngày: <strong><?= count($revenueData) ?></strong> | 
        Tổng đơn hàng: <strong><?= array_sum(array_column($revenueData, 'total_orders')) ?></strong>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Ngày</th>
                <th class="text-right">Số đơn hàng</th>
                <th class="text-right">Doanh thu</th>
                <th class="text-right">Giá trị đơn trung bình</th>
                <th class="text-center">Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($revenueData as $row): ?>
                <tr>
                    <td><?= date('d/m/Y', strtotime($row['order_date'])) ?></td>
                    <td class="text-right"><?= number_format($row['total_orders'], 0) ?></td>
                    <td class="text-right highlight"><?= number_format($row['total_revenue'], 0, ',', '.') ?> ₫</td>
                    <td class="text-right"><?= number_format($row['avg_order_value'], 0, ',', '.') ?> ₫</td>
                    <td class="text-center">
                        <button onclick="loadContent('/modules/statistics/admin_order_list_by_date.php?date=<?= $row['order_date'] ?>')"class="btn-btn">Xem chi tiết đơn hàng</button>
                    </td>
                </tr>
            <?php endforeach; ?>
            <?php if (empty($revenueData)): ?>
                <tr>
                    <td colspan="5" class="text-center">Không có dữ liệu doanh thu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>