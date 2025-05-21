<?php
session_start();
include __DIR__ . '/../../config/config.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';

if ($keyword) {
    $sql = "SELECT * FROM users WHERE role = 'employee' AND (fullname LIKE :keyword OR email LIKE :keyword)";
    $stmt = $conn->prepare($sql);
    $stmt->execute(['keyword' => "%$keyword%"]);
} else {
    $sql = "SELECT * FROM users WHERE role = 'employee'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
}

$result = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<h2>Danh sách nhân viên</h2>

<form method="GET" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
    <input type="text" name="keyword" placeholder="Nhập tên hoặc email tìm kiếm" value="<?php echo htmlspecialchars($keyword); ?>">
    <button type="submit">Tìm kiếm</button>
</form>

<a href="/modules/employees/add.php">Thêm Nhân Viên</a>

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
                <a href="/modules/employees/edit.php?id=<?php echo $row['id']; ?>">Sửa</a>
                <a href="/modules/employees/delete.php?id=<?php echo $row['id']; ?>" onclick="return confirm('Bạn có chắc chắn muốn xóa nhân viên này không?');">Xóa</a>
            </td>
        </tr>
    <?php } ?>
</table>
