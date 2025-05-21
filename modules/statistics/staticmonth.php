<?php
session_start();
include __DIR__ . '/../../config/config.php';

// Truy vấn doanh thu theo tháng
$stmt = $conn->prepare("
    SELECT 
        DATE_FORMAT(o.created_at, '%Y-%m') as month,
        COUNT(o.id) as total_orders,
        SUM(o.total_price) as total_revenue,
        AVG(o.total_price) as avg_order_value
    FROM orders o
    GROUP BY DATE_FORMAT(o.created_at, '%Y-%m')
    ORDER BY month DESC
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
    <title>Báo cáo doanh thu theo tháng</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
            margin-bottom: 20px;
        }
        .summary {
            background-color: #f8f9fa;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 1.2em;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 10px;
            text-align: left;
        }
        th {
            background-color: #343a40;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .highlight {
            font-weight: bold;
            background-color: #e7f5ff;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .view-btn {
            padding: 3px 8px;
            background-color: #17a2b8;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            font-size: 0.9em;
        }
    </style>
</head>
<body>
    <h1>Báo cáo doanh thu theo tháng</h1>
    
    <div class="summary">
        Tổng doanh thu: <strong><?= number_format($totalRevenue, 0, ',', '.') ?> ₫</strong> | 
        Tổng số tháng: <strong><?= count($revenueData) ?></strong> | 
        Tổng đơn hàng: <strong><?= array_sum(array_column($revenueData, 'total_orders')) ?></strong>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Tháng/Năm</th>
                <th class="text-right">Số đơn hàng</th>
                <th class="text-right">Doanh thu</th>
                <th class="text-right">Giá trị đơn trung bình</th>
                <th class="text-center">Chi tiết</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($revenueData as $row): 
                $monthName = date('m/Y', strtotime($row['month'].'-01'));
            ?>
                <tr>
                    <td><?= $monthName ?></td>
                    <td class="text-right"><?= number_format($row['total_orders'], 0) ?></td>
                    <td class="text-right highlight"><?= number_format($row['total_revenue'], 0, ',', '.') ?> ₫</td>
                    <td class="text-right"><?= number_format($row['avg_order_value'], 0, ',', '.') ?> ₫</td>
                    <td class="text-center">
                        <a href="/modules/statistics/admin_order_by_month.php?month=<?= $row['month'] ?>" class="view-btn">Xem đơn hàng</a>
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