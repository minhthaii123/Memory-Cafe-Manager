<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
include __DIR__ . '/../../config/config.php';

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    // Xử lý upload ảnh
    $image = '';
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = 'assets/images/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    }

    // Thêm sản phẩm vào cơ sở dữ liệu
    $sql = "INSERT INTO products (product_name, price, quantity, description, image, created_at)
            VALUES (:product_name, :price, :quantity, :description, :image, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_name', $product_name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':image', $image);

    if ($stmt->execute()) {
        echo "Thêm sản phẩm thành công!";
        header("Location: /trangadmin.php");
        exit;
    } else {
        echo "Có lỗi xảy ra. Vui lòng thử lại.";
    }
}
?>

<h2>Thêm sản phẩm mới</h2>
<link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
<form action="/modules/products/add.php" method="post" enctype="multipart/form-data">
    <label>Tên sản phẩm:</label><br>
    <input type="text" name="product_name" required><br><br>

    <label>Giá (VND):</label><br>
    <input type="number" name="price" required><br><br>

    <label>Số lượng:</label><br>
    <input type="number" name="quantity" required><br><br>

    <label>Mô tả:</label><br>
    <textarea name="description" rows="4" required></textarea><br><br>

    <label>Chọn ảnh sản phẩm:</label><br>
    <input type="file" name="image" accept="image/*" required><br><br>

    <input type="submit" value="Thêm sản phẩm">
</form>
