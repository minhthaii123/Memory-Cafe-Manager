<?php
session_start();
include __DIR__ . '/../../config/config.php';
// Phần xử lý GET - Hiển thị form chỉnh sửa
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];

    $sql = "SELECT * FROM users WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':id', $id);
    $stmt->execute();
    $employee = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$employee) {
        echo "Nhân viên không tồn tại!";
        exit;
    }
} elseif ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    // Phần xử lý POST - Cập nhật dữ liệu nhân viên
    $id = $_POST['id'];
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $salary = $_POST['salary'];

    $sql = "UPDATE users SET fullname = :fullname, email = :email, phone = :phone, salary = :salary WHERE id = :id";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':phone', $phone);
    $stmt->bindParam(':salary', $salary);
    $stmt->bindParam(':id', $id);

    if ($stmt->execute()) {
        echo "Cập nhật nhân viên thành công!";
        header("Location: /trangadmin.php");
        exit;
    } else {
        echo "Cập nhật thất bại!";
    }
} else {
    echo "Không tìm thấy ID nhân viên. Vui lòng kiểm tra lại.";
    exit;
}
?>

<h2>Sửa thông tin nhân viên</h2>
<link rel="stylesheet" type="text/css" href="/assets/css/styles.css">
<form action="edit.php" method="post">
    <input type="hidden" name="id" value="<?php echo isset($employee['id']) ? $employee['id'] : ''; ?>">
    <label>Họ tên:</label><br>
    <input type="text" name="fullname" value="<?php echo $employee['fullname'] ?? ''; ?>" required><br><br>

    <label>Email:</label><br>
    <input type="email" name="email" value="<?php echo $employee['email'] ?? ''; ?>" required><br><br>

    <label>Số điện thoại:</label><br>
    <input type="text" name="phone" value="<?php echo $employee['phone'] ?? ''; ?>" required><br><br>

    <label>Lương (VND):</label><br>
    <input type="number" name="salary" value="<?php echo $employee['salary'] ?? ''; ?>" required><br><br>

    <input type="submit" value="Cập nhật">
</form>
