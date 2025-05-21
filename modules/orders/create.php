<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    die("Lỗi: Chưa đăng nhập");
}
include __DIR__ . '/../../config/config.php'; // File cấu hình kết nối database
// Lấy danh sách sản phẩm từ database
$products = [];
try {
    $stmt = $conn->query("SELECT id, product_name, price, image FROM products");
    $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    die("Lỗi kết nối database: " . $e->getMessage());
}

// Tạo mảng ánh xạ tên file ảnh sang thông tin sản phẩm
$productMap = [];
foreach ($products as $product) {
    $imageName = basename($product['image']);
    $productMap[$imageName] = [
        'id' => $product['id'],
        'name' => $product['product_name'],
        'price' => $product['price']
    ];
}
$productMapJson = json_encode($productMap);
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tạo Đơn Hàng</title>
    <link rel="stylesheet" href="styles.css">
    <link rel="stylesheet" href="/assets/css/create.css">

</head>
<body>
    <div class="container">
        <h2>Tạo Đơn Hàng</h2>
        <form id="orderForm">
            <label for="orderCount">Số lượng đơn:</label>
            <input type="number" id="orderCount" name="orderCount" min="1" value="1">
            <button type="button" id="generateBtn">Tạo đơn hàng</button>
        </form>
    </div>

    <div class="container" id="ordersContainer">
        <div class="order-row" id="orderContainer"></div>
    </div>

    <div class="container total-price" id="totalPrice">Tổng giá tiền: 0</div>
    <button type="button" id="submitOrderBtn">Xác nhận tạo đơn hàng</button>
    <button><a href="/trangadmin.php"style="text-decoration: none; color: inherit;">← Quay lại danh Chính</a></button>


    <script>
        // Truyền dữ liệu sản phẩm từ PHP sang JavaScript
        const productMap = <?php echo $productMapJson; ?>;
        
        document.addEventListener('DOMContentLoaded', function() {
            const generateBtn = document.getElementById('generateBtn');
            generateBtn.addEventListener('click', generateOrders);
        });

        function generateOrders() {
            const orderCount = parseInt(document.getElementById("orderCount").value);
            const ordersContainer = document.getElementById("orderContainer");
            
            if (isNaN(orderCount) || orderCount < 1) {
                alert('Vui lòng nhập số lượng đơn hợp lệ');
                return;
            }

            ordersContainer.innerHTML = "";

            for (let i = 0; i < orderCount; i++) {
                const orderItem = document.createElement("div");
                orderItem.className = "order-item";
                orderItem.innerHTML = `
                    <h3>Đơn hàng ${i + 1}</h3>
                    <label for="productImage${i}">Ảnh sản phẩm:</label>
                    <input type="file" id="productImage${i}" class="image-upload" data-index="${i}" accept="image/*">
                    <img id="imagePreview${i}" class="preview-image" style="display:none;">
                    
                    <label for="productName${i}">Tên sản phẩm:</label>
                    <input type="text" id="productName${i}" placeholder="Tên sản phẩm" readonly>

                    <label for="productId${i}">Mã sản phẩm:</label>
                    <input type="text" id="productId${i}" readonly>

                    <label for="quantity${i}">Số lượng:</label>
                    <input type="number" id="quantity${i}" value="1" min="1" data-index="${i}" class="price-input">

                    <label for="unitPrice${i}">Đơn giá:</label>
                    <input type="number" id="unitPrice${i}" value="0" min="0" data-index="${i}" class="price-input" readonly>

                    <p>Tổng tiền đơn hàng: <span id="orderTotal${i}" class="order-total">0</span></p>
                `;
                ordersContainer.appendChild(orderItem);
            }

            // Thêm sự kiện cho các input giá và số lượng
            document.querySelectorAll('.price-input').forEach(input => {
                input.addEventListener('input', function() {
                    calculatePrice(this.dataset.index);
                });
            });

            // Thêm sự kiện cho upload ảnh
            document.querySelectorAll('.image-upload').forEach(input => {
                input.addEventListener('change', function() {
                    handleImageUpload(this);
                });
            });

            calculateTotalPrice();
        }

        function handleImageUpload(input) {
            const index = input.dataset.index;
            const file = input.files[0];
            const preview = document.getElementById(`imagePreview${index}`);
            const productNameInput = document.getElementById(`productName${index}`);
            const productIdInput = document.getElementById(`productId${index}`);
            const unitPriceInput = document.getElementById(`unitPrice${index}`);

            if (file) {
                // Hiển thị ảnh xem trước
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                }
                reader.readAsDataURL(file);

                // Tìm sản phẩm tương ứng với tên file ảnh
                const fileName = file.name;
                const productInfo = productMap[fileName];
                
                if (productInfo) {
                    // Điền thông tin sản phẩm nếu tìm thấy
                    productNameInput.value = productInfo.name;
                    productIdInput.value = productInfo.id;
                    unitPriceInput.value = productInfo.price;
                    
                    // Tính toán lại giá
                    calculatePrice(index);
                } else {
                    // Nếu không tìm thấy sản phẩm
                    productNameInput.value = 'Không tìm thấy sản phẩm';
                    productIdInput.value = '';
                    unitPriceInput.value = '0';
                }
            } else {
                preview.style.display = 'none';
                productNameInput.value = '';
                productIdInput.value = '';
                unitPriceInput.value = '0';
            }
        }

        function calculatePrice(orderIndex) {
            const quantity = parseFloat(document.getElementById(`quantity${orderIndex}`).value) || 0;
            const unitPrice = parseFloat(document.getElementById(`unitPrice${orderIndex}`).value) || 0;
            const orderTotal = document.getElementById(`orderTotal${orderIndex}`);

            orderTotal.textContent = (quantity * unitPrice).toLocaleString();
            calculateTotalPrice();
        }

        function calculateTotalPrice() {
            const orderTotals = document.querySelectorAll('.order-total');
            let total = 0;

            orderTotals.forEach(order => {
                total += parseFloat(order.textContent.replace(/,/g, '')) || 0;
            });

            document.getElementById("totalPrice").textContent = 
                `Tổng giá tiền: ${total.toLocaleString()}K VND` ;
                }
                document.getElementById('submitOrderBtn').addEventListener('click', function () {
            const orders = [];
            const count = parseInt(document.getElementById("orderCount").value);

            for (let i = 0; i < count; i++) {
                const productId = document.getElementById(`productId${i}`).value;
                const quantity = parseInt(document.getElementById(`quantity${i}`).value);
                const unitPrice = parseFloat(document.getElementById(`unitPrice${i}`).value);

                if (productId && quantity > 0 && unitPrice >= 0) {
                    orders.push({
                        product_id: productId,
                        quantity: quantity,
                        unit_price: unitPrice
                    });
                }
            }

            if (orders.length === 0) {
                alert("Vui lòng chọn ít nhất một sản phẩm hợp lệ.");
                return;
            }

            const total = orders.reduce((sum, item) => sum + item.quantity * item.unit_price, 0);

            fetch('/modules/orders/save_order.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ orders: orders, total_price: total })
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    alert("Tạo đơn hàng thành công!");
                    location.reload();
                    
                } else {
                    alert("Lỗi: " + data.message);
                }
            })
            .catch(error => {
                alert("Lỗi khi gửi đơn hàng.");
                console.error(error);
            });
        });

    </script>
</body>



</html>