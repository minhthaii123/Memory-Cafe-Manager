<?php
include __DIR__ . '/../config/config.php'; 

try {
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $query = "SELECT product_name, price, image, quantity FROM products";
    
    if (!empty($search)) {
        $query .= " WHERE product_name LIKE :search";
        $stmt = $conn->prepare($query);
        $stmt->execute(['search' => "%$search%"]);
    } else {
        $stmt = $conn->query($query);
    }
    
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

        .search-container {
            text-align: center;
            margin: 20px auto;
            max-width: 500px;
            display: flex;
            justify-content: center;
        }
        
        .search-container form {
            display: flex;
            width: 100%;
            gap: 10px;
        }
        
        .search-container input[type="text"] {
            flex: 2; /* Chiếm 2 phần */
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            outline: none;
        }
        
        .search-container input[type="text"]:focus {
            border-color: #4CAF50;
            box-shadow: 0 0 5px rgba(76, 175, 80, 0.2);
        }
        
        .search-container button {
            flex: 1; /* Chiếm 1 phần (1/2 của input) */
            padding: 10px;
            background-color: #4CAF50;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        
        .search-container button:hover {
            background-color: #45a049;
        }
    </style>
</head>
<body>
    <h2 style="text-align: center;">Danh sách sản phẩm</h2>
    
    <div class="search-container">
        <form action="" method="GET">
            <input type="text" name="search" placeholder="Tìm kiếm sản phẩm..." value="<?= htmlspecialchars($search) ?>">
            <button type="submit">Tìm kiếm</button>
        </form>
    </div>
    
    <div class="product-list">
        <?php foreach ($products as $product): ?>
            <div class="product-card">
                <img src="/<?= htmlspecialchars($product['image']) ?>" alt="Ảnh sản phẩm">
                <h3><?= htmlspecialchars($product['product_name']) ?></h3>
                <p><strong>Giá:</strong> <?= number_format($product['price'], 0, ',', '.') ?> VNĐ</p>
            </div>
        <?php endforeach; ?>
    </div>
</body>
</html>
