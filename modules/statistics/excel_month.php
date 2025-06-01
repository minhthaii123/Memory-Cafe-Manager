<a href="?export=excel" class="export-btn"><h4 style="background-color: #28a745;" background-color:rgb(10, 238, 33);>Xuất Excel static_month</h4></a><br>
<?php
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

// Nếu có yêu cầu xuất file Excel
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

    // Format cột tiền tệ (C và D)
    $currencyFormat = '#,##0₫';
    $sheet->getStyle('C2:C' . ($rowNum - 1))->getNumberFormat()->setFormatCode($currencyFormat);
    $sheet->getStyle('D2:D' . ($rowNum - 1))->getNumberFormat()->setFormatCode($currencyFormat);

    // Thiết lập header cho tải file Excel
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="bao_cao_doanh_thu_' . date('Ymd_His') . '.xlsx"');
    header('Cache-Control: max-age=0');

    // Xuất file
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
}
?>