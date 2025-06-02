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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Lấy lại dữ liệu sản phẩm hiện tại để phòng trường hợp lỗi
    $id = $_GET['id'] ?? null;
    $stmt = $conn->prepare("SELECT * FROM products WHERE id = :id");
    $stmt->execute(['id' => $id]);
    $product = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$product) {
        echo "Sản phẩm không tồn tại!";
        exit;
    }

    $product_name = $_POST['product_name'];
    $price = $_POST['price'];
    $quantity = $_POST['quantity'];
    $description = $_POST['description'];

    $image = $product['image']; // Mặc định giữ ảnh cũ
    if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] == 0) {
        $imagePath = 'assets/images/' . basename($_FILES['image']['name']);
        if (move_uploaded_file($_FILES['image']['tmp_name'], $imagePath)) {
            $image = $imagePath;
        }
    }

    $sql = "UPDATE products 
            SET product_name = :product_name, price = :price, quantity = :quantity,
                description = :description, image = :image 
            WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $success = $stmt->execute([
        'product_name' => $product_name,
        'price' => $price,
        'quantity' => $quantity,
        'description' => $description,
        'image' => $image,
        'id' => $id
    ]);

    if ($success) {
        header("Location: /trangadmin.php");
        exit;
    } else {
        echo "Cập nhật thất bại!";
    }
}

?>

<h2>Sửa sản phẩm</h2>
<link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
<form method="post" enctype="multipart/form-data">
    <label>Tên sản phẩm:</label>
    <input type="text" name="product_name" value="<?php echo $product['product_name']; ?>" required>

    <label>Giá (VND):</label>
    <input type="number" name="price" value="<?php echo $product['price']; ?>" required>

    <label>Số lượng:</label>
    <input type="number" name="quantity" value="<?php echo $product['quantity']; ?>" required>

    <label>Mô tả:</label>
    <textarea name="description" rows="4"><?php echo $product['description']; ?></textarea>

    <label>Ảnh sản phẩm:</label>
    <input type="file" name="image">

    <img src="/<?php echo $product['image']; ?>" width="100" alt="Ảnh hiện tại">

    <input type="submit" value="Cập nhật">
</form>
