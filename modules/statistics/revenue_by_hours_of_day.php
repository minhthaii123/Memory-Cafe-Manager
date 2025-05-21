<?php
include __DIR__ . '/../../config/config.php';

// Lấy ngày từ tham số URL, mặc định là hôm nay
$selectedDate = $_GET['date'] ?? date('Y-m-d');
$prevDate = date('Y-m-d', strtotime($selectedDate . ' -1 day'));
$nextDate = date('Y-m-d', strtotime($selectedDate . ' +1 day'));

// Truy vấn doanh thu theo giờ của ngày được chọn
$stmt = $conn->prepare("
    SELECT 
        HOUR(o.created_at) as hour,
        COUNT(o.id) as total_orders,
        SUM(o.total_price) as total_revenue,
        AVG(o.total_price) as avg_order_value
    FROM orders o
    WHERE DATE(o.created_at) = ?
    GROUP BY HOUR(o.created_at)
    ORDER BY hour ASC
");
$stmt->execute([$selectedDate]);
$revenueByHour = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Tính tổng doanh thu trong ngày
$totalRevenue = array_sum(array_column($revenueByHour, 'total_revenue'));

// Chuẩn bị dữ liệu cho biểu đồ (đảm bảo đủ 24 giờ)
$chartLabels = [];
$chartData = [];
for ($i = 0; $i < 24; $i++) {
    $found = false;
    foreach ($revenueByHour as $row) {
        if ($row['hour'] == $i) {
            $chartLabels[] = sprintf("%02d:00-%02d:00", $i, $i+1);
            $chartData[] = $row['total_revenue'];
            $found = true;
            break;
        }
    }
    if (!$found) {
        $chartLabels[] = sprintf("%02d:00-%02d:00", $i, $i+1);
        $chartData[] = 0;
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doanh thu theo giờ ngày <?= $selectedDate ?></title>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .date-navigator {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 5px;
        }
        .date-navigator a {
            padding: 5px 10px;
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 4px;
        }
        .date-navigator h2 {
            margin: 0;
        }
        .summary {
            background-color: #e9ecef;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            font-size: 1.1em;
        }
        .chart-container {
            margin: 30px 0;
            height: 400px;
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
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .peak-hour {
            background-color: #ffcccc;
        }
        .normal-hour {
            background-color: #ffffff;
        }
        .no-data {
            color: #999;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="date-navigator">
        <a href="?date=<?= $prevDate ?>">← Ngày trước</a>
        <h2>Doanh thu theo giờ - Ngày <?= date('d/m/Y', strtotime($selectedDate)) ?></h2>
        <a href="?date=<?= $nextDate ?>">Ngày sau →</a>
    </div>
    
    <div class="summary">
        <strong>Tổng doanh thu:</strong> <?= number_format($totalRevenue, 0, ',', '.') ?> ₫ | 
        <strong>Tổng đơn hàng:</strong> <?= array_sum(array_column($revenueByHour, 'total_orders')) ?> |
        <strong>Giá trị đơn trung bình:</strong> <?= $totalRevenue > 0 ? number_format($totalRevenue/array_sum(array_column($revenueByHour, 'total_orders')), 0, ',', '.') : '0' ?> ₫
    </div>
    
    <div class="chart-container">
        <canvas id="hourlyRevenueChart"></canvas>
    </div>
    
    <table>
        <thead>
            <tr>
                <th>Khung giờ</th>
                <th class="text-right">Số đơn hàng</th>
                <th class="text-right">Doanh thu</th>
                <th class="text-right">Đơn giá trung bình</th>
                <th class="text-center">Tỉ trọng doanh thu</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $peakHours = [11, 12, 13, 18, 19, 20]; // Giờ cao điểm
            foreach ($chartLabels as $index => $label): 
                $hour = $index;
                $isPeak = in_array($hour, $peakHours);
                $rowClass = $isPeak ? 'peak-hour' : 'normal-hour';
                
                // Tìm dữ liệu tương ứng
                $data = null;
                foreach ($revenueByHour as $row) {
                    if ($row['hour'] == $hour) {
                        $data = $row;
                        break;
                    }
                }
                
                $revenue = $data ? $data['total_revenue'] : 0;
                $percentage = $totalRevenue > 0 ? ($revenue / $totalRevenue) * 100 : 0;
            ?>
                <tr class="<?= $rowClass ?>">
                    <td><?= $label ?></td>
                    <td class="text-right"><?= $data ? number_format($data['total_orders'], 0) : '<span class="no-data">không có</span>' ?></td>
                    <td class="text-right highlight"><?= $data ? number_format($data['total_revenue'], 0, ',', '.') : '<span class="no-data">không có</span>' ?> ₫</td>
                    <td class="text-right"><?= $data ? number_format($data['avg_order_value'], 0, ',', '.') : '<span class="no-data">không có</span>' ?> ₫</td>
                    <td class="text-center"><?= $totalRevenue > 0 ? number_format($percentage, 1) . '%' : '0%' ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <script>
        // Vẽ biểu đồ doanh thu theo giờ
        const ctx = document.getElementById('hourlyRevenueChart').getContext('2d');
        const hourlyRevenueChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?= json_encode($chartLabels) ?>,
                datasets: [{
                    label: 'Doanh thu theo giờ (₫)',
                    data: <?= json_encode($chartData) ?>,
                    backgroundColor: [
                        <?php 
                        foreach ($chartLabels as $index => $label) {
                            $hour = $index;
                            $isPeak = in_array($hour, [11, 12, 13, 18, 19, 20]);
                            echo $isPeak ? "'rgba(255, 99, 132, 0.7)'," : "'rgba(54, 162, 235, 0.7)',";
                        }
                        ?>
                    ],
                    borderColor: [
                        <?php 
                        foreach ($chartLabels as $index => $label) {
                            $hour = $index;
                            $isPeak = in_array($hour, [11, 12, 13, 18, 19, 20]);
                            echo $isPeak ? "'rgba(255, 99, 132, 1)'," : "'rgba(54, 162, 235, 1)',";
                        }
                        ?>
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Doanh thu (₫)'
                        },
                        ticks: {
                            callback: function(value) {
                                return value.toLocaleString() + ' ₫';
                            }
                        }
                    },
                    x: {
                        title: {
                            display: true,
                            text: 'Khung giờ trong ngày'
                        }
                    }
                },
                plugins: {
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Doanh thu: ' + context.raw.toLocaleString() + ' ₫';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>