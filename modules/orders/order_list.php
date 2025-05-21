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
    SELECT o.id, o.total_price, o.status, o.created_at,
           COUNT(od.id) as item_count
    FROM orders o
    LEFT JOIN order_details od ON o.id = od.order_id
    WHERE o.user_id = ?
    GROUP BY o.id
    ORDER BY o.created_at DESC
");
$stmt->execute([$userId]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Danh sách đơn hàng của bạn</h2>
<table border="1" cellpadding="8">
    <tr>
        <th>ID</th>
        <th>Số mặt hàng</th>
        <th>Tổng tiền</th>
        <th>Trạng thái</th>
        <th>Ngày tạo</th>
        <th>Chi tiết</th>
        <th>Hành Động</th>
    </tr>
    <?php foreach ($orders as $order): ?>
        <tr>
            <td><?= htmlspecialchars($order['id']) ?></td>
            <td><?= $order['item_count'] ?></td>
            <td><?= number_format($order['total_price'], 0, ',', '.') ?> ₫</td>
            <td><?= htmlspecialchars($order['status']) ?></td>
            <td><?= date('d/m/Y H:i:s', strtotime($order['created_at'])) // Định dạng ngày tháng ?></td>
            <td><a href="order_detail.php?id=<?= $order['id'] ?>">Xem</a></td>
            <td>
                <?php
                if ($order['status'] !== 'Hoàn thành' && $order['status'] !== 'Hủy'):
                ?>
                    <a href="/modules/orders/update_status.php?order_id=<?= $order['id'] ?>&status=<?= urlencode('Hoàn thành') ?>"><button>✅ Hoàn thành</button></a>
                    <a href="/modules/orders/update_status.php?order_id=<?= $order['id'] ?>&status=<?= urlencode('Hủy') ?>"><button>❌ Hủy</button></a>
                <?php else: ?>
                    <!-- Không có hành động nếu đã hoàn thành hoặc hủy -->
                    Đã <?= htmlspecialchars(strtolower($order['status'])) ?>
                <?php endif; ?>
            </td>
        </tr>
    <?php endforeach; ?>
    <?php if (empty($orders)): ?>
        <tr>
            <td colspan="7">Bạn chưa có đơn hàng nào.</td>
        </tr>
    <?php endif; ?>
    
</table>
