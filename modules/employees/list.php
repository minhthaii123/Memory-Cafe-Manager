<?php
session_start();
include __DIR__ . '/../../config/config.php';

    $sql = "SELECT * FROM users WHERE role = 'employee'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
    .btn-xem {
        background-color:rgb(40, 215, 13); /* màu đỏ */
        color: white;
        padding: 6px 12px;
        text-decoration: none;
        border-radius: 4px;
        display: inline-block;
        }
        .btn-delete:hover {
        background-color:rgb(14, 210, 73); /* đỏ đậm hơn khi hover */
        }
</style>
<h2>Danh sách nhân viên</h2>

<button onclick="loadContent('/modules/employees/add.php')">thêm nhân viên mới</button>

<table border="1" cellspacing="0" cellpadding="10">
    <tr>
        <th>Họ tên</th>
        <th>Email</th>
        <th>Số điện thoại</th>
        <th>Lương</th>
        <th>Ngày Thêm</th>
        <th>Hành động</th>
    </tr>
    <?php foreach ($result as $row) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['fullname']); ?></td>
            <td><?php echo htmlspecialchars($row['email']); ?></td>
            <td><?php echo htmlspecialchars($row['phone']); ?></td>
            <td><?php echo number_format($row['salary'], 0, ',', '.'); ?> VND</td>
            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
            <td>
                <a href="/modules/employees/edit.php?id=<?php echo $row['id']; ?>')"class="btn-xem">Sửa</button>
                <a href="/modules/employees/delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa nhân viên này không?');" class="btn-delete">Xóa</a>
            </td>
        </tr>
    <?php } ?>
</table>
