<?php
session_start();

include __DIR__ . '/../../config/config.php';

// Kiểm tra có ID nhân viên cần xóa không
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    // Kiểm tra nhân viên có tồn tại không
    $checkSql = "SELECT * FROM users WHERE id = :id AND role = 'employee'";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->bindParam(':id', $id);
    $checkStmt->execute();

    if ($checkStmt->rowCount() === 0) {
        echo "Nhân viên không tồn tại!";
        exit;
    }

    // Thực hiện xóa nhân viên
    $deleteSql = "DELETE FROM users WHERE id = :id AND role = 'employee'";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->bindParam(':id', $id);

    if ($deleteStmt->execute()) {
        echo "Xóa nhân viên thành công!";
        header("Location: list.php");
        exit;
    } else {
        echo "Lỗi: Không thể xóa nhân viên.";
    }
} else {
    echo "Không tìm thấy ID nhân viên cần xóa.";
}
?>
