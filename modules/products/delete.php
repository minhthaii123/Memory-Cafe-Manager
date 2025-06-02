<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
include __DIR__ . '/../../config/config.php';

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Xóa các chi tiết đơn hàng liên quan đến sản phẩm này
    $sql = "DELETE FROM order_details WHERE product_id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();

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
        header("Location: /trangadmin.php");
        exit;
    } else {
        echo "Có lỗi xảy ra!";
    }
}
