<?php
include __DIR__ . '/../config/config.php'; 

try {
    $stmt = $conn->query("SELECT product_name, price, image, quantity FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi truy vấn: " . $e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Danh sách sản phẩm</title>
    <style>
        .product-list {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
            padding: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .product-card {
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }

        .product-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-radius: 5px;
            margin-bottom: 10px;
        }

        .product-card h3 {
            margin: 10px 0;
            font-size: 1.2em;
            color: #333;
        }

        .product-card p {
            margin: 5px 0;
            color: #666;
        }

        @media (max-width: 900px) {
            .product-list {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (max-width: 600px) {
            .product-list {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Danh sách sản phẩm</h2>
    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="/<?= htmlspecialchars($product['image']) ?>" alt="Ảnh sản phẩm">
                <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                <p><strong>Giá:</strong> <?= number_format($product['price'], 0, ',', '.') ?> VNĐ</p>
                <p><strong>Số lượng:</strong> <?= $product['quantity'] ?></p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
