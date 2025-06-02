<?php
session_start();
include __DIR__ . '/../../config/config.php';

require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

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
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="stylesheet" href="/assets/css/staticmonth.css">
    <title>Báo cáo doanh thu theo tháng</title>
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
            <?php if (!empty($revenueData)): ?>
                <?php foreach ($revenueData as $row): 
                    $monthName = date('m/Y', strtotime($row['month'].'-01'));
                ?>
                    <tr>
                        <td><?= htmlspecialchars($monthName) ?></td>
                        <td class="text-right"><?= number_format($row['total_orders'], 0) ?></td>
                        <td class="text-right highlight"><?= number_format($row['total_revenue'], 0, ',', '.') ?> ₫</td>
                        <td class="text-right"><?= number_format($row['avg_order_value'], 0, ',', '.') ?> ₫</td>
                        <td class="text-center">
                            <button onclick="loadContent('/modules/statistics/admin_order_by_month.php?month=<?= htmlspecialchars($row['month']) ?>')"class="btn-btn">Xem chi tiết đơn hàng</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center">Không có dữ liệu doanh thu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
