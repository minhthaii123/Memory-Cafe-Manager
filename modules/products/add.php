<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

include __DIR__ . '/../../config/config.php';

// Xử lý khi submit form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $product_name = trim($_POST['product_name']);
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = trim($_POST['description']);

    // Kiểm tra tên sản phẩm đã tồn tại chưa
    $checkStmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_name = :product_name");
    $checkStmt->bindParam(':product_name', $product_name);
    $checkStmt->execute();
    $exists = $checkStmt->fetchColumn();

    if ($exists > 0) {
        echo "<p style='color:red;'>Tên sản phẩm <strong>$product_name</strong> đã tồn tại. Vui lòng chọn tên khác.</p>";
    } else {
        // Xử lý upload ảnh
        $image = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $targetDir = 'assets/images/';
            $imageName = basename($_FILES['image']['name']);
            $image = $targetDir . time() . '_' . $imageName;// Tránh trùng tên file
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
            echo "<p style='color:green;'>Thêm sản phẩm thành công!</p>";
            header("Location: /trangadmin.php");
            exit;
        } else {
            echo "<p style='color:red;'>Có lỗi xảy ra khi thêm sản phẩm. Vui lòng thử lại.</p>";
        }
    }
}
?>

<h2>Thêm sản phẩm mới</h2>
<link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
<form action="/modules/products/add.php" method="post" enctype="multipart/form-data">
    <label>Tên sản phẩm:</label>
    <input type="text" name="product_name" required>

    <label>Giá (VND):</label>
    <input type="number" name="price" required>

    <label>Số lượng:</label>
    <input type="number" name="quantity" required>

    <label>Mô tả:</label>
    <textarea name="description" rows="4" required></textarea>

    <label>Chọn ảnh sản phẩm:</label>
    <input type="file" name="image" accept="image/*" required>

    <input type="submit" value="Thêm sản phẩm">
</form>
