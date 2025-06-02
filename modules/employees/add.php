<?php
session_start();
include __DIR__ . '/../../config/config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $salary = $_POST['salary'];
    $username = $_POST['username'];
    $password = $_POST['password']; // giữ nguyên mật khẩu không băm
    $role = 'employee';

    $sql = "INSERT INTO users (fullname, email, phone, salary, username, password, role, created_at)
            VALUES (:fullname, :email, :phone, :salary, :username, :password, :role, NOW())";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':salary', $salary);
    $stmt->bindParam(':username', $username);
    $stmt->bindParam(':password', $password);
    $stmt->bindParam(':role', $role);

    if ($stmt->execute()) {
        echo "Thêm nhân viên thành công!";
        header('Location: /trangadmin.php');
        exit;
    } else {
        echo "Có lỗi xảy ra, vui lòng thử lại.";
    }
}

?>

<link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
<form action="/modules/employees/add.php" method="post">
    <h2>Thêm Nhân Viên</h2>
    <label>Họ tên:</label>
    <input type="text" name="fullname" required>

    <label>Email:</label>
    <input type="email" name="email" required>

    <label>Số điện thoại:</label>
    <input type="text" name="phone" required>

    <label>Lương (VND):</label>
    <input type="number" name="salary" required>

    <label>Tên đăng nhập:</label>
    <input type="text" name="username" required>

    <label>Mật khẩu:</label>
    <input type="password" name="password" required>

    <input type="submit" value="Thêm nhân viên">
</form>
