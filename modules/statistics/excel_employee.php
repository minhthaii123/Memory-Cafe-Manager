<a href="?export=excel" class="export-btn"><h4 style="background-color: #28a745;" background-color:rgb(10, 238, 33);>Xuất Excel revenue_by_employee</h4></a>
<?php 
    if (isset($_GET['export']) && $_GET['export'] == 'excel') {
    // Lấy dữ liệu như cũ
    $stmt = $conn->prepare("
        SELECT 
            u.id as user_id,
            u.fullname,
            u.username,
            COUNT(o.id) as order_count,
            SUM(o.total_price) as total_revenue,
            MAX(o.created_at) as last_order_date
        FROM 
            orders o
        JOIN 
            users u ON o.user_id = u.id
        WHERE 
            u.role = 'employee'
        GROUP BY 
            u.id, u.fullname, u.username
        ORDER BY 
            total_revenue DESC
    ");
    $stmt->execute();
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Tạo file Excel
    require_once __DIR__ . '/../../vendor/autoload.php';
    
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    
    // Đặt tiêu đề
    $sheet->setTitle("Doanh thu nhân viên");
    
    // Tiêu đề cột với định dạng đẹp hơn
    $sheet->setCellValue('A1', 'STT');
    $sheet->setCellValue('B1', 'Nhân viên');
    $sheet->setCellValue('C1', 'Tài khoản');
    $sheet->setCellValue('D1', 'Số đơn hàng');
    $sheet->setCellValue('E1', 'Tổng doanh thu');
    $sheet->setCellValue('F1', 'Đơn hàng gần nhất');
    
    // Định dạng tiêu đề
    $headerStyle = [
        'font' => [
            'bold' => true,
            'color' => ['rgb' => 'FFFFFF'],
        ],
        'fill' => [
            'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
            'startColor' => ['rgb' => '3498db'],
        ],
        'alignment' => [
            'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        ],
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
            ],
        ],
    ];
    $sheet->getStyle('A1:F1')->applyFromArray($headerStyle);
    
    // Đổ dữ liệu
    $row = 2;
    foreach ($employees as $index => $employee) {
        $sheet->setCellValue('A'.$row, $index + 1);
        $sheet->setCellValue('B'.$row, $employee['fullname']);
        $sheet->setCellValue('C'.$row, $employee['username']);
        $sheet->setCellValue('D'.$row, $employee['order_count']);
        $sheet->setCellValue('E'.$row, $employee['total_revenue']);
        $sheet->setCellValue('F'.$row, date('d/m/Y H:i', strtotime($employee['last_order_date'])));
        
        // Định dạng hàng xen kẽ
        if ($row % 2 == 0) {
            $sheet->getStyle('A'.$row.':F'.$row)
                  ->getFill()
                  ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                  ->getStartColor()->setRGB('f8f9fa');
        }
        $row++;
    }
    
    // Định dạng cột doanh thu
    $sheet->getStyle('E2:E'.$row)
          ->getNumberFormat()
          ->setFormatCode('#,##0" ₫"');
    
    // Định dạng border cho toàn bộ dữ liệu
    $dataStyle = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                'color' => ['rgb' => 'dddddd'],
            ],
        ],
        'alignment' => [
            'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
        ],
    ];
    $sheet->getStyle('A1:F'.($row-1))->applyFromArray($dataStyle);
    
    // Căn giữa các cột số
    $sheet->getStyle('A1:A'.($row-1))->getAlignment()->setHorizontal('center');
    $sheet->getStyle('D1:D'.($row-1))->getAlignment()->setHorizontal('center');
    
    // Tự động điều chỉnh độ rộng cột
    foreach (range('A','F') as $columnID) {
        $sheet->getColumnDimension($columnID)->setAutoSize(true);
    }
    
    // Đặt tên file với ngày xuất báo cáo
    $filename = 'Bao_cao_doanh_thu_nhan_vien_' . date('Ymd_His') . '.xlsx';
    
    // Tạo header để download
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    header('Expires: 0');
    header('Pragma: public');
    
    // Tạo file và tải về
    $writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, 'Xlsx');
    $writer->save('php://output');
    exit;
}

// Lấy doanh thu theo nhân viên (cho hiển thị trên web)
$stmt = $conn->prepare("
    SELECT 
        u.id as user_id,
        u.fullname,
        u.username,
        COUNT(o.id) as order_count,
        SUM(o.total_price) as total_revenue,
        MAX(o.created_at) as last_order_date
    FROM 
        orders o
    JOIN 
        users u ON o.user_id = u.id
    WHERE 
        u.role = 'employee'
    GROUP BY 
        u.id, u.fullname, u.username
    ORDER BY 
        total_revenue DESC
");
$stmt->execute();
$employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>