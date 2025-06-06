<?php
session_start();
include __DIR__ . '/../../config/config.php';

if (!isset($_SESSION['user_id'])) {
    echo "Vui lòng đăng nhập để xem đơn hàng.";
    exit;
}

$userId = $_SESSION['user_id'];

// Giả sử trạng thái ban đầu của đơn hàng là 'pending' hoặc một giá trị khác
// Khi tạo đơn hàng, bạn nên đặt một trạng thái mặc định, ví dụ: 'Chờ xử lý'
$stmt = $conn->prepare("
    SELECT 
        o.id, 
        o.total_price, 
        o.status, 
        o.created_at,
        COUNT(od.id) AS item_count,
        GROUP_CONCAT(p.product_name SEPARATOR ', ') AS product_names
    FROM orders o
    LEFT JOIN order_details od ON o.id = od.order_id
    LEFT JOIN products p ON od.product_id = p.id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");

$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<style>
    .order-table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 20px;
        background-color: #fff;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
    }

    .order-table th, .order-table td {
        padding: 12px;
        text-align: left;
        border: 1px solid #ddd;
    }

    .order-table th {
        background-color: #f5f5f5;
        font-weight: bold;
        color: #333;
    }

    .order-table tr:hover {
        background-color: #f9f9f9;
    }

    /* Định dạng trạng thái */
    .status {
        padding: 6px 12px;
        border-radius: 4px;
        font-weight: bold;
        display: inline-block;
    }

    .status-completed {
        background-color: #e8f5e9;
        color: #2e7d32;
        border: 1px solid #a5d6a7;
    }

    .status-cancelled {
        background-color: #ffebee;
        color: #c62828;
        border: 1px solid #ef9a9a;
    }

    /* Định dạng nút */
    .btn {
        padding: 6px 12px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-weight: bold;
        margin: 0 4px;
        transition: background-color 0.3s;
    }

    .btn-complete {
        background-color: #4caf50;
        color: white;
    }

    .btn-complete:hover {
        background-color: #45a049;
    }

    .btn-cancel {
        background-color: #f44336;
        color: white;
    }

    .btn-cancel:hover {
        background-color: #da190b;
    }

    /* Định dạng tiêu đề */
    .order-title {
        color: #333;
        margin-bottom: 20px;
        font-size: 24px;
    }

    /* Định dạng thông báo không có đơn hàng */
    .no-orders {
        text-align: center;
        padding: 20px;
        color: #666;
        font-style: italic;
    }
</style>

<h2 class="order-title">Danh sách đơn hàng của bạn</h2>
<table class="order-table">
    <tr>
        <th>ID</th>
        <th>Số đơn hàng</th>
        <th>Sản phẩm</th>
        <th>Tổng tiền</th>
        <th>Trạng thái</th>
        <th>Ngày tạo</th>
        <th>Hành Động</th>
    </tr>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= htmlspecialchars($order['id']) ?></td>
            <td><?= $order['item_count'] ?></td>
            <td><?= htmlspecialchars($order['product_names']) ?></td>
            <td><?= number_format($order['total_price'], 0, ',', '.') ?> ₫</td>
            <td>
                <span class="status <?= $order['status'] === 'Hoàn thành' ? 'status-completed' : 
                                    ($order['status'] === 'Hủy' ? 'status-cancelled' : '') ?>">
                    <?= htmlspecialchars($order['status']) ?>
                </span>
            </td>
            <td><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) ?></td>
            <td>
                <?php if ($order['status'] !== 'Hoàn thành' && $order['status'] !== 'Hủy'): ?>
                    <a href="/modules/orders/update_status.php?order_id=<?= $order['id'] ?>&status=<?= urlencode('Hoàn thành') ?>">
                        <button class="btn btn-complete">✅ Hoàn thành</button>
                    </a>
                    <a href="/modules/orders/update_status.php?order_id=<?= $order['id'] ?>&status=<?= urlencode('Hủy') ?>">
                        <button class="btn btn-cancel">❌ Hủy</button>
                    </a>
                <?php else: ?>
                    <span class="status <?= $order['status'] === 'Hoàn thành' ? 'status-completed' : 'status-cancelled' ?>">
                        Đã <?= htmlspecialchars(strtolower($order['status'])) ?>
                    </span>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?>
        <tr>
            <td colspan="8" class="no-orders">Bạn chưa có đơn hàng nào.</td>
        </tr>
    <?php endif; ?>
</table>
