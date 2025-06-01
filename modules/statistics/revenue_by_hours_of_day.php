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
    <link rel="stylesheet" href="/assets/css/revenue_by_hours_of_day.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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