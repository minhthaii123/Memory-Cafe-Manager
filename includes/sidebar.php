<?php

include __DIR__ . '/../config/config.php'; 
// Kiểm tra đăng nhập
if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit();
}

// Lấy thông tin user từ session
$user = $_SESSION['user'];
$role = $user['role'] ?? 'employee'; // Mặc định là employee nếu không có role
?>

<link rel="stylesheet" href="/assets/css/sidebar.css">

<div class="sidebar">
    <p style="color: red;">SDT: 0412312</p>
    <a href="/trangadmin.php">
        <img src="/assets/images/logo.png" alt="logo">
    </a>

   <button onclick="window.location.href = '/modules/orders/create.php';">Tạo đơn hàng</button>
    <button onclick="loadContent('/modules/orders/order_list.php')">Đơn hàng của tôi</button>

<?php if ($role === 'admin'): ?>
    <button onclick="loadContent('/modules/products/list.php')">Quản lý sản phẩm</button>
    <button onclick="loadContent('/modules/orders/totallist.php')">Quản lý đơn hàng</button>
    <button onclick="loadContent('/modules/employees/list.php')">Quản lý nhân viên</button>
    <button onclick="loadContent('/modules/statistics/staticday.php')">Doanh thu hàng ngày</button>
    <button onclick="loadContent('/modules/statistics/staticmonth.php')">Doanh thu hàng tháng</button>
    <button onclick="loadContent('/modules/statistics/staticyear.php')">Doanh thu hàng năm</button>
    <button onclick="loadContent('/modules/statistics/top_products.php')">Top sản phẩm bán chạy</button>
    <button onclick="loadContent('/modules/statistics/revenue_by_employee.php')">Doanh thu theo nhân viên</button>
<?php endif; ?>

    
</div>
<script src="/assets/js/sidebar.js"></script>
