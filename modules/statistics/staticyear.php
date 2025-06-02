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
    <link rel="stylesheet" href="/assets/css/staticyear.css">
    <title>Báo cáo doanh thu theo năm</title>
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
                                <button onclick="loadContent('/modules/statistics/admin_order_by_month.php?month=<?= $yearData['year'] ?>-<?= str_pad($monthData['month'], 2, '0', STR_PAD_LEFT) ?>')"class="btn-btn">Xem chi tiết đơn hàng</button>
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