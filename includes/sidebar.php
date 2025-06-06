
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

<!--   <button onclick="window.location.href = '/modules/orders/create.php';">Tạo đơn hàng</button>-->
   <button onclick="showOrderModal()">Tạo đơn hàng </button>

    <button onclick="loadContent('/modules/orders/order_list.php')">Đơn hàng của tôi</button>

<?php if ($role === 'admin'): ?>
    <button onclick="loadContent('/modules/products/list.php')">Quản lý sản phẩm</button>
    <button onclick="loadContent('/modules/orders/totallist.php')">Quản lý đơn hàng</button>
    <button onclick="loadContent('/modules/orders/order_huy.php')">Các đơn hàng bị hủy</button>
    <button onclick="loadContent('/modules/employees/list.php')">Quản lý nhân viên</button>
    <button onclick="loadContent('/modules/statistics/staticday.php')">Doanh thu hàng ngày</button>
    <button onclick="loadContent('/modules/statistics/staticmonth.php')">Doanh thu hàng tháng</button>
    <button onclick="loadContent('/modules/statistics/staticyear.php')">Doanh thu hàng năm</button>
    <button onclick="loadContent('/modules/statistics/top_products.php')">Top sản phẩm bán chạy</button>
    <button onclick="loadContent('/modules/statistics/revenue_by_employee.php')">Doanh thu theo nhân viên</button>
<?php endif; ?>

    
</div>
<script>
function showOrderModal() {
    document.getElementById('modal').style.display = 'block';
}

function showOrderModal() {
    // Tránh tạo nhiều lần
    if (document.getElementById("orderMiniPopup")) return;

    const iframe = document.createElement("iframe");
    iframe.src = "/modules/orders/create.php";
    Object.assign(iframe.style, {
        position: "fixed",
        top: "50%",
        left: "50%",
        transform: "translate(-50%, -50%)",
        width: "900px",
        height: "900px",
        zIndex: "9900",
        border: "1px solid #ccc",
        borderRadius: "10px",
        background: "white",
        pointerEvents: "auto"
    });
    iframe.id = "orderMiniPopup";

    const overlay = document.createElement("div");
    overlay.id = "orderOverlay";
    Object.assign(overlay.style, {
        position: "fixed",
        top: 0,
        left: 0,
        width: "100%",
        height: "100%",
        backgroundColor: "rgba(0,0,0,0.4)",
        zIndex: "9899",  // thấp hơn iframe
        pointerEvents: "auto"
    });

    overlay.addEventListener("click", () => {
        document.body.removeChild(iframe);
        document.body.removeChild(overlay);
    });

    document.body.appendChild(overlay);
    document.body.appendChild(iframe);
}

</script>

