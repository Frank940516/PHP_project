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
    <title>賣家儀表板</title>
</head>
<body>
    <h1>歡迎，賣家 <?php echo $_SESSION['username']; ?>！</h1>
    <ul>
        <li><a href="my_products.php">商品管理</a></li>
        <li><a href="product_add.php">上架商品</a></li>
        <li><a href="orders.php">訂單管理</a></li>
        <li><a href="profile_edit.php">修改個人資料</a></li>
        <li><a href="/logout.php">登出</a></li>
    </ul>
</body>
</html>
