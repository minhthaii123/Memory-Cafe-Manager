<?php
session_start();
include __DIR__ . '/../../config/config.php';

// Lấy thông tin sản phẩm theo ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM products WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $product = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$product) {
        echo "Sản phẩm không tồn tại!";
        exit;
    }
}

// Xử lý cập nhật sản phẩm
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    // Kiểm tra có upload ảnh mới không
    if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $image = 'assets/images/' . basename($_FILES['image']['name']);
        move_uploaded_file($_FILES['image']['tmp_name'], $image);
    } else {
        $image = $product['image'];
    }

    $sql = "UPDATE products 
            SET product_name = :product_name, price = :price, quantity = :quantity, 
                description = :description, image = :image 
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':product_name', $product_name);
    $stmt->bindParam(':price', $price);
    $stmt->bindParam(':quantity', $quantity);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':image', $image);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "Cập nhật sản phẩm thành công!";
        header("Location: /trangadmin.php");
        exit;
    } else {
        echo "Có lỗi xảy ra!";
    }
}
?>

<h2>Sửa sản phẩm</h2>
<link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
<form method="post" enctype="multipart/form-data">
    <label>Tên sản phẩm:</label><br>
    <input type="text" name="product_name" value="<?php echo $product['product_name']; ?>" required><br><br>

    <label>Giá (VND):</label><br>
    <input type="number" name="price" value="<?php echo $product['price']; ?>" required><br><br>

    <label>Số lượng:</label><br>
    <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" required><br><br>

    <label>Mô tả:</label><br>
    <textarea name="description" rows="4"><?php echo $product['description']; ?></textarea><br><br>

    <label>Ảnh sản phẩm:</label><br>
    <input type="file" name="image"><br><br>

    <img src="/<?php echo $product['image']; ?>" width="100" alt="Ảnh hiện tại"><br><br>

    <input type="submit" value="Cập nhật">
</form>
