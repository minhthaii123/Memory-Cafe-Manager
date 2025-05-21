<?php
session_start();
include __DIR__ . '/../../config/config.php';

// Kiểm tra quyền admin
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Kiểm tra nếu không phải admin thì không được xem
$checkRole = $conn->prepare("SELECT role FROM users WHERE id = ?");
$checkRole->execute([$_SESSION['user_id']]);
$user = $checkRole->fetch(PDO::FETCH_ASSOC);

if ($user['role'] !== 'admin') {
    die("Bạn không có quyền truy cập trang này");
}

// Lấy doanh thu theo nhân viên
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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Doanh Thu Theo Nhân Viên</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        th {
            background-color: #4CAF50;
            color: white;
        }
        tr:nth-child(even) {
            background-color: #f2f2f2;
        }
        .highlight {
            font-weight: bold;
            color: #e67e22;
        }
        .badge {
            background-color: #3498db;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 14px;
        }
        .export-btn {
            margin: 20px 0;
            padding: 10px 15px;
            background-color: #27ae60;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        .export-btn:hover {
            background-color: #2ecc71;
        }
    </style>
</head>
<body>
    <h1>Báo Cáo Doanh Thu Theo Nhân Viên</h1>
    
    <button class="export-btn" onclick="exportToExcel()">Xuất Excel</button>
    
    <table id="revenueTable">
        <thead>
            <tr>
                <th>STT</th>
                <th>Nhân viên</th>
                <th>Tài khoản</th>
                <th>Số đơn hàng</th>
                <th>Tổng doanh thu</th>
                <th>Đơn hàng gần nhất</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($employees as $index => $employee): ?>
                <tr>
                    <td><?= $index + 1 ?></td>
                    <td><?= htmlspecialchars($employee['fullname']) ?></td>
                    <td><?= htmlspecialchars($employee['username']) ?></td>
                    <td><span class="badge"><?= $employee['order_count'] ?></span></td>
                    <td class="highlight"><?= number_format($employee['total_revenue'], 0, ',', '.') ?> ₫</td>
                    <td><?= date('d/m/Y H:i', strtotime($employee['last_order_date'])) ?></td>
                    
                </tr>
            <?php endforeach; ?>
            
            <?php if (empty($employees)): ?>
                <tr>
                    <td colspan="7" style="text-align: center;">Không có dữ liệu doanh thu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <script>
        function exportToExcel() {
            // Tạo một bảng HTML tạm thời
            let html = '<table>';
            
            // Thêm tiêu đề
            html += '<tr>';
            html += '<th>STT</th>';
            html += '<th>Nhân viên</th>';
            html += '<th>Tài khoản</th>';
            html += '<th>Số đơn hàng</th>';
            html += '<th>Tổng doanh thu</th>';
            html += '<th>Đơn hàng gần nhất</th>';
            html += '</tr>';
            
            // Thêm dữ liệu
            <?php foreach ($employees as $index => $employee): ?>
                html += '<tr>';
                html += '<td><?= $index + 1 ?></td>';
                html += '<td><?= htmlspecialchars($employee['fullname']) ?></td>';
                html += '<td><?= htmlspecialchars($employee['username']) ?></td>';
                html += '<td><?= $employee['order_count'] ?></td>';
                html += '<td><?= number_format($employee['total_revenue'], 0, ',', '.') ?> ₫</td>';
                html += '<td><?= date('d/m/Y H:i', strtotime($employee['last_order_date'])) ?></td>';
                html += '</tr>';
            <?php endforeach; ?>
            
            html += '</table>';
            
            // Tạo blob và tải về
            let blob = new Blob([html], {type: 'application/vnd.ms-excel'});
            let link = document.createElement('a');
            link.href = URL.createObjectURL(blob);
            link.download = 'doanh_thu_nhan_vien.xls';
            link.click();
        }
    </script>
</body>
</html>