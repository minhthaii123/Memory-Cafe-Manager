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

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/revenue_by_employee.css">
    <title>Doanh Thu Theo Nhân Viên</title>
</head>
<body>
    <h1>Báo Cáo Doanh Thu Theo Nhân Viên</h1>
    
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
                    <td colspan="6" style="text-align: center;">Không có dữ liệu doanh thu</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
    
</body>
</html>
