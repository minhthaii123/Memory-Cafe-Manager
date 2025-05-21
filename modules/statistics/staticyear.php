<?php
session_start();
include __DIR__ . '/../../config/config.php';

// Truy vấn doanh thu theo năm
$stmt = $conn->prepare("
    SELECT 
        YEAR(o.created_at) as year,
        COUNT(o.id) as total_orders,
        SUM(o.total_price) as total_revenue,
        AVG(o.total_price) as avg_order_value,
        MONTH(o.created_at) as month
    FROM orders o
    GROUP BY YEAR(o.created_at), MONTH(o.created_at)
    ORDER BY year DESC, month DESC
");
$stmt->execute();
$rawData = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tổ chức dữ liệu theo năm
$revenueData = [];
foreach ($rawData as $row) {
    $year = $row['year'];
    if (!isset($revenueData[$year])) {
        $revenueData[$year] = [
            'year' => $year,
            'total_orders' => 0,
            'total_revenue' => 0,
            'months' => []
        ];
    }
    $revenueData[$year]['total_orders'] += $row['total_orders'];
    $revenueData[$year]['total_revenue'] += $row['total_revenue'];
    $revenueData[$year]['months'][] = $row;
}

// Tính tổng doanh thu
$totalRevenue = array_sum(array_column($revenueData, 'total_revenue'));
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Báo cáo doanh thu theo năm</title>
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
        .year-section {
            margin-bottom: 30px;
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
        }
        .year-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
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
        .expand-btn {
            padding: 3px 8px;
            background-color: #6c757d;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <h1>Báo cáo doanh thu theo năm</h1>
    
    <div class="summary">
        Tổng doanh thu: <strong><?= number_format($totalRevenue, 0, ',', '.') ?> ₫</strong> | 
        Tổng số năm: <strong><?= count($revenueData) ?></strong> | 
        Tổng đơn hàng: <strong><?= array_sum(array_column($revenueData, 'total_orders')) ?></strong>
    </div>
    
    <?php foreach ($revenueData as $yearData): ?>
        <div class="year-section">
            <div class="year-header">
                <h2>Năm <?= $yearData['year'] ?></h2>
                <div>
                    <strong>Tổng đơn hàng:</strong> <?= number_format($yearData['total_orders'], 0) ?> | 
                    <strong>Tổng doanh thu:</strong> <?= number_format($yearData['total_revenue'], 0, ',', '.') ?> ₫
                </div>
            </div>
            
            <table>
                <thead>
                    <tr>
                        <th>Tháng</th>
                        <th class="text-right">Số đơn hàng</th>
                        <th class="text-right">Doanh thu</th>
                        <th class="text-right">Giá trị đơn trung bình</th>
                        <th class="text-center">Chi tiết</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($yearData['months'] as $monthData): 
                        $monthName = date('m/Y', strtotime($yearData['year'].'-'.$monthData['month'].'-01'));
                    ?>
                        <tr>
                            <td><?= $monthName ?></td>
                            <td class="text-right"><?= number_format($monthData['total_orders'], 0) ?></td>
                            <td class="text-right highlight"><?= number_format($monthData['total_revenue'], 0, ',', '.') ?> ₫</td>
                            <td class="text-right"><?= number_format($monthData['avg_order_value'], 0, ',', '.') ?> ₫</td>
                            <td class="text-center">
                                <a href="/modules/statistics/admin_order_by_month.php?month=<?= $yearData['year'] ?>-<?= str_pad($monthData['month'], 2, '0', STR_PAD_LEFT) ?>" class="view-btn">Xem đơn hàng</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endforeach; ?>
    
    <?php if (empty($revenueData)): ?>
        <div class="text-center">Không có dữ liệu doanh thu</div>
    <?php endif; ?>
</body>
</html>