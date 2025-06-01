<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}
include __DIR__ . '/../../config/config.php';

// Xử lý tìm kiếm nếu có từ khóa
$sql = "SELECT * FROM products";
$params = [];

if (isset($_GET['search']) && !empty($_GET['search'])) {
    $sql .= " WHERE product_name LIKE :search";
    $params[':search'] = '%' . $_GET['search'] . '%';
}

$stmt = $conn->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<style>
.btn-delete {
  background-color: #e74c3c; /* màu đỏ */
  color: white;
  padding: 6px 12px;
  text-decoration: none;
  border-radius: 4px;
  display: inline-block;
}
.btn-delete:hover {
  background-color: #c0392b; /* đỏ đậm hơn khi hover */
}
</style>
<h2>Danh sách sản phẩm</h2>

<!-- Form tìm kiếm trên cùng file -->
<form method="GET" action="/modules/products/list.php">
    <input type="text" name="search" placeholder="Tìm sản phẩm">
    <button type="submit">Tìm</button>
</form>


<!-- Nút thêm sản phẩm -->
    <button onclick="loadContent('/modules/products/add.php')">hêm sản phẩm mới</button>
<!-- Bảng danh sách -->
<table border="1" cellspacing="0" cellpadding="10">
    <tr>
        <th>ID</th>
        <th>Tên sản phẩm</th>
        <th>Giá</th>
        <th>Số lượng</th>
        <th>Mô tả</th>
        <th>Ảnh</th>
        <th>Ngày tạo</th>
        <th>Hành động</th>
    </tr>
    <?php if (count($products) > 0): ?>
        <?php foreach ($products as $product): ?>
            <tr>
                <td><?= $product['id']; ?></td>
                <td><?= $product['product_name']; ?></td>
                <td><?= number_format($product['price'], 0, ',', '.'); ?> VND</td>
                <td><?= $product['quantity']; ?></td>
                <td><?= $product['description']; ?></td>
                <td><img src="/<?= $product['image']; ?>" width="100" alt="Ảnh sản phẩm"></td>
                <td><?= $product['created_at']; ?></td>
                <td>
                    <button onclick="loadContent('/modules/products/edit.php?id=<?= $product['id']; ?>')">Sửa</button>
                    <a href="/modules/products/delete.php?id=<?= $product['id']; ?>" 
                            onclick="return confirm('Bạn có chắc chắn muốn xóa sản phẩm này không?')" 
                            class="btn-delete">
                            Xóa
                    </a>

                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="8">Không tìm thấy sản phẩm nào.</td></tr>
    <?php endif; ?>
</table>
