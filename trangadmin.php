<?php 
if (session_status() == PHP_SESSION_NONE) {
    session_start(); // Khởi tạo session nếu chưa có session nào hoạt động
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Memory Cafe Manager</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="/assets/css/trangadmin.css">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>

    <?php include("./includes/sidebar.php"); ?>

    <div class="content">
        <div class="header">
            <h1>Memory Cafe Manager</h1> 
            <p>
                <strong style="color: red;">
                    Xin chào, <?= htmlspecialchars($_SESSION['user']['username'] ?? '') ?>
                </strong>
            </p>
        </div>
        <!-- Nội dung mặc định khi vào trangadmin.php -->
        <div id="main-content">
            <?php 
                if (isset($_SESSION['user']) && $_SESSION['user']['role'] === 'admin') {
                    include("./modules/statistics/revenue_by_hours_of_day.php");
                } else {
                    include("./includes/pruducts.php");
                }
            ?>
        </div>
    </div>

    <!-- Đăng xuất -->
    <a href="/logout.php" class="logout-btn">
        <p class="fas fa-sign-out-alt"></p> Đăng xuất
    </a>

    <!-- Script load content qua AJAX -->
    <script>
        function loadContent(url) {
            const xhr = new XMLHttpRequest();
            xhr.open('GET', url, true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === 4 && xhr.status === 200) {
                    document.getElementById('main-content').innerHTML = xhr.responseText;
                }
            };
            xhr.send();
        }
    </script>

    
</body>
</html>
