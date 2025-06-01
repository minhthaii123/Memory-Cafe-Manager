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

// Xử lý xuất file Excel nếu có yêu cầu
if (isset($_GET['export']) && $_GET['export'] === 'excel') {
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Tiêu đề bảng
    $sheet->setCellValue('A1', 'Tháng/Năm');
    $sheet->setCellValue('B1', 'Số đơn hàng');
    $sheet->setCellValue('C1', 'Doanh thu (₫)');
    $sheet->setCellValue('D1', 'Giá trị đơn trung bình (₫)');

    // Đổ dữ liệu từng dòng
    $rowNum = 2;
    foreach ($revenueData as $row) {
        $monthName = date('m/Y', strtotime($row['month'].'-01'));
        $sheet->setCellValue('A' . $rowNum, $monthName);
        $sheet->setCellValue('B' . $rowNum, $row['total_orders']);
        $sheet->setCellValue('C' . $rowNum, $row['total_revenue']);
        $sheet->setCellValue('D' . $rowNum, round($row['avg_order_value']));
        $rowNum++;
    }

    // Format cột tiền tệ (cột C và D)
    $currencyFormat = '#,##0₫';
    $sheet->getStyle('C2:C' . ($rowNum - 1))->getNumberFormat()->setFormatCode($currencyFormat);
    $sheet->getStyle('D2:D' . ($rowNum - 1))->getNumberFormat()->setFormatCode($currencyFormat);

    // Tạo writer
    $writer = new Xlsx($spreadsheet);
    
    // Thiết lập headers để tải file về
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="bao_cao_doanh_thu_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');
    
    // Gửi file về trình duyệt
    $writer->save('php://output');
    exit;
}
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
    <a href="?export=excel" class="export-btn">Xuất Excel</a>

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
                            <a href="/modules/statistics/admin_order_by_month.php?month=<?= htmlspecialchars($row['month']) ?>" class="view-btn">Xem đơn hàng</a>
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