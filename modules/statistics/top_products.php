<?php
session_start();
include __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Chưa đăng nhập");
}

// Lấy top sản phẩm bán chạy
try {
    $stmt = $conn->prepare("
        SELECT 
            p.id,
            p.product_name,
            p.price,
            p.image,
            SUM(od.quantity) as total_sold,
            SUM(od.quantity * od.unit_price) as total_revenue
        FROM 
            order_details od
        JOIN 
            products p ON od.product_id = p.id
        GROUP BY 
            p.id, p.product_name, p.price, p.image
        ORDER BY 
            total_sold DESC
        LIMIT 10
    ");
    $stmt->execute();
    $topProducts = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Top Sản Phẩm Bán Chạy</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        h1 {
            color: #333;
            text-align: center;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .product-image {
            max-width: 100px;
            height: auto;
        }
        .badge {
            background-color: #ff9900;
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <h1>Top 10 Sản Phẩm Bán Chạy</h1>
    
    <table border="1" cellspacing="0" cellpadding="10">
        <tr>
            <th>STT</th>
            <th>Tên sản phẩm</th>
            <th>Ảnh</th>
            <th>Giá</th>
            <th>Đã bán</th>
            <th>Doanh thu</th>
        </tr>
        <?php foreach ($topProducts as $index => $product): ?>
            <tr>
                <td><?= $index + 1 ?></td>
                <td><?= htmlspecialchars($product['product_name']) ?></td>
                <td>
                    <?php if (!empty($product['image'])): ?>
                        <img src="/<?= htmlspecialchars($product['image']) ?>" class="product-image" alt="<?= htmlspecialchars($product['product_name']) ?>" 
                             onerror="this.onerror=null;this.src='/assets/images/no-image.jpg'">
                    <?php else: ?>
                        <img src="/assets/images/no-image.jpg" class="product-image" alt="No image">
                    <?php endif; ?>
                </td>
                <td><?= number_format($product['price'], 0, ',', '.') ?> VND</td>
                <td><span class="badge"><?= $product['total_sold'] ?></span></td>
                <td><?= number_format($product['total_revenue'], 0, ',', '.') ?> VND</td>
            </tr>
        <?php endforeach; ?>
        
        <?php if (empty($topProducts)): ?>
            <tr>
                <td colspan="6">Chưa có dữ liệu bán hàng.</td>
            </tr>
        <?php endif; ?>
    </table>
</body>
</html>