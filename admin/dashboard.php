<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location:/login.php");
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>管理者儀表板</title>
</head>
<body>
    <h1>歡迎，管理者 <?php echo $_SESSION['username']; ?>！</h1>
    <ul>
        <li><a href="user_manage.php">會員管理</a></li>
        <li><a href="order_manage.php">商品審核</a></li>
        <li><a href="news_manage.php">最新消息管理</a></li>
        <li><a href="edit_profile.php">修改個人資料</a></li>
        <li><a href="/logout.php">登出</a></li>
    </ul>
</body>
</html>
