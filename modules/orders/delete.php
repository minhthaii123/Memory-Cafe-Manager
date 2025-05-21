<?php
session_start();
include __DIR__ . '/../../config/config.php';

if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // Kiểm tra đơn hàng có tồn tại không
    $checkSql = "SELECT * FROM orders WHERE id = :id";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':id', $id, PDO::PARAM_INT);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        echo "Đơn Hàng không tồn tại!";
        exit;
    }

    $conn->beginTransaction();

    try {
        // Xóa các bản ghi liên quan trong order_details
        $deleteDetailsSql = "DELETE FROM order_details WHERE order_id = :id";
        $deleteDetailsStmt = $conn->prepare($deleteDetailsSql);
        $deleteDetailsStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $deleteDetailsStmt->execute();

        // Xóa đơn hàng
        $deleteOrderSql = "DELETE FROM orders WHERE id = :id";
        $deleteOrderStmt = $conn->prepare($deleteOrderSql);
        $deleteOrderStmt->bindParam(':id', $id, PDO::PARAM_INT);
        $deleteOrderStmt->execute();

        $conn->commit();

        // Lưu thông báo thành công vào session
        $_SESSION['success'] = "Xóa đơn hàng thành công.";

        // Chuyển hướng về trang quản lý
        header("Location: /trangadmin.php");
        exit;

    } catch (PDOException $e) {
        $conn->rollBack();
        echo "Lỗi khi xóa đơn hàng: " . $e->getMessage();
    }
} else {
    echo "Không tìm thấy ID Đơn Hàng cần xóa.";
}
