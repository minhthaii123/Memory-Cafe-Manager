<?php
session_start();
include __DIR__ . '/config/config.php'; // Kết nối PDO

// Xử lý đăng nhập
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch();

    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: trangadmin.php');
        $_SESSION['user_id'] = $user['id'];
        exit();
    } else {
        $error = "Sai tên đăng nhập hoặc mật khẩu!";
        header('Location: login.php?error=' . urlencode($error));
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="/assets/css/login.css">
    <title>Đăng nhập</title>
</head>
<body>
    
    
    <form action="login.php" method="post">
        <h2>Đăng nhập</h2>
        <?php if (isset($_GET['error'])): ?>
             <p style='color:red;'><?= htmlspecialchars($_GET['error']) ?></p>
        <?php endif; ?>
        <input type="text" name="username" placeholder="Tên đăng nhập" required><br><br>
        <input type="password" name="password" placeholder="Mật khẩu" required><br><br>
        <button type="submit">Đăng nhập</button>
    </form>
</body>
</html>