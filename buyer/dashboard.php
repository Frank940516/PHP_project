<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['role'])) {
    header("Location: /login.php");
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>買家儀表板</title>
</head>
<body>
    <h1>歡迎，買家 <?php echo $_SESSION['username']; ?>！</h1>
    <ul>
        <li><a href="orders.php">查看訂單</a></li>
        <li><a href="cart.php">購物車</a></li>
        <li><a href="profile_edit.php">修改個人資料</a></li>
        <li><a href="/logout.php">登出</a></li>
    </ul>
</body>
</html>