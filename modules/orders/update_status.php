<?php
session_start();
include __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$orderId = $_GET['order_id'] ?? null;
$newStatus = $_GET['status'] ?? null; // Renamed to newStatus for clarity

if (!$orderId || !$newStatus) {
    echo "Thiếu thông tin ID đơn hàng hoặc trạng thái mới.";
    exit;
}

// Kiểm tra xem đơn hàng có tồn tại không và thuộc quyền của người dùng
$checkStmt = $conn->prepare("SELECT status FROM orders WHERE id = ? AND user_id = ?");
$checkStmt->execute([$orderId, $_SESSION['user_id']]);
$currentOrder = $checkStmt->fetch(PDO::FETCH_ASSOC);

if (!$currentOrder) {
    echo "Không tìm thấy đơn hàng hoặc bạn không có quyền cập nhật đơn hàng này.";
    exit;
}

if ($currentOrder['status'] === 'Hoàn thành' || $currentOrder['status'] === 'Hủy') {
    echo "Đơn hàng đã ở trạng thái cuối cùng, không thể cập nhật.";
    // header("Location: order_list.php");
    exit;
}


// Cập nhật trạng thái đơn hàng
$allowedStatuses = ['Hoàn thành', 'Hủy']; // Các trạng thái được phép từ URL
if (in_array($newStatus, $allowedStatuses)) {
    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND user_id = ?");
    $success = $updateStmt->execute([$newStatus, $orderId, $_SESSION['user_id']]);

    if ($success) {
        $_SESSION['message'] = "Cập nhật trạng thái đơn hàng #{$orderId} thành công.";
    } else {
        $_SESSION['message'] = "Có lỗi xảy ra khi cập nhật trạng thái đơn hàng.";
    }
} else {
    $_SESSION['message'] = "Trạng thái cập nhật không hợp lệ.";
    // echo "Trạng thái cập nhật không hợp lệ.";
    // exit;
}

// Chuyển hướng lại trang danh sách đơn hàng
header("Location: /trangadmin.php");
exit;
?>