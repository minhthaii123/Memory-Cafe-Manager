<?php
session_start();
include __DIR__ . '/../../config/config.php';
require __DIR__ . '/../../vendor/autoload.php'; // Đường dẫn đến autoload Composer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

// Truy vấn tên nhân viên từ user_id trong session
$userStmt = $conn->prepare("SELECT fullname FROM users WHERE id = ?");
$userStmt->execute([$_SESSION['user_id']]);
$user = $userStmt->fetch(PDO::FETCH_ASSOC);
$employeeName = $user ? $user['fullname'] : 'Không xác định';

$orderId = $_GET['order_id'] ?? null;
$newStatus = $_GET['status'] ?? null; // Trạng thái mới

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
    echo "Đơn hàng đang ở trạng thái cuối cùng, không thể cập nhật.";
    exit;
}

// Cập nhật trạng thái đơn hàng
$allowedStatuses = ['Hoàn thành', 'Hủy']; // Các trạng thái được phép cập nhật
if (in_array($newStatus, $allowedStatuses)) {
    $updateStmt = $conn->prepare("UPDATE orders SET status = ? WHERE id = ? AND user_id = ?");
    $success = $updateStmt->execute([$newStatus, $orderId, $_SESSION['user_id']]);

    if ($success) {
        $_SESSION['message'] = "Cập nhật đơn hàng #{$orderId} thành công.";

        // Gửi email thông báo khi hủy đơn hàng
        if ($newStatus === 'Hủy') {
            $mail = new PHPMailer(true);

            try {
                // Cấu hình SMTP Gmail
                $mail->isSMTP();
                $mail->Host = 'smtp.gmail.com';
                $mail->SMTPAuth = true;
                $mail->Username = 'minhthai070620@gmail.com';
                $mail->Password = 'ihgmylhvknvoagxa';
                $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
                $mail->Port = 587;

                // Người gửi và người nhận
                $mail->setFrom('minhthai070620@gmail.com', 'Hệ thống đơn hàng');
                $mail->addAddress('minhthai070620@gmail.com'); // Người nhận là chính bạn

                // Nội dung email
                $mail->isHTML(true);
                $mail->CharSet = 'UTF-8';
                $mail->Encoding = 'base64';
                $mail->Subject = "Thông báo đơn hàng đã bị hủy";
                $mail->Body = "Đơn hàng <strong>#{$orderId}</strong> đã bị hủy bởi nhân viên <strong>{$employeeName}</strong>.";

                $mail->send(); // Gửi email
            } catch (Exception $e) {
                error_log("Lỗi gửi email: " . $mail->ErrorInfo);
            }
        }

    } else {
        $_SESSION['message'] = "Có lỗi xảy ra khi cập nhật trạng thái đơn hàng.";
    }
} else {
    $_SESSION['message'] = "Trạng thái cập nhật không hợp lệ.";
}

// Chuyển hướng lại trang danh sách đơn hàng
header("Location: /trangadmin.php");
exit;
