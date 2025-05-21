<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
include __DIR__ . '/../../config/config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Xóa ảnh khỏi thư mục uploads
    $sql = "SELECT image FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($product && file_exists($product['image'])) {
        unlink($product['image']);
    }

    // Xóa sản phẩm khỏi database
    $sql = "DELETE FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "Xóa sản phẩm thành công!";
        header("Location: /trangadmin.php");
        exit;
    } else {
        echo "Có lỗi xảy ra!";
    }
}
?>
