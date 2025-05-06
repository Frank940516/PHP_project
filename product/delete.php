<?php
require('../db.inc'); // 資料庫連線檔案
session_start();

// 檢查是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$userEmail = $_SESSION['user'];

// 查詢使用者 ID
$sqlUser = "SELECT No FROM accounts WHERE Email = ?";
$stmtUser = mysqli_prepare($link, $sqlUser);
mysqli_stmt_bind_param($stmtUser, 's', $userEmail);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);

if (!$user) {
    echo "使用者不存在！";
    exit();
}

$userId = $user['No'];

// 確認商品 ID
if (!isset($_GET['id'])) {
    echo "商品 ID 不存在！";
    exit();
}

$productId = intval($_GET['id']);

// 查詢商品資料
$sqlProduct = "SELECT attachment FROM products WHERE id = ? AND seller_id = ?";
$stmtProduct = mysqli_prepare($link, $sqlProduct);
mysqli_stmt_bind_param($stmtProduct, 'ii', $productId, $userId);
mysqli_stmt_execute($stmtProduct);
$resultProduct = mysqli_stmt_get_result($stmtProduct);
$product = mysqli_fetch_assoc($resultProduct);

if (!$product) {
    echo "商品不存在或無權限刪除！";
    exit();
}

// 標記商品為已刪除
$sqlMarkDeleted = "UPDATE products SET is_deleted = 1 WHERE id = ? AND seller_id = ?";
$stmtMarkDeleted = mysqli_prepare($link, $sqlMarkDeleted);
mysqli_stmt_bind_param($stmtMarkDeleted, 'ii', $productId, $userId);
mysqli_stmt_execute($stmtMarkDeleted);

if (mysqli_stmt_affected_rows($stmtMarkDeleted) > 0) {
    echo "<script>alert('商品已成功移除！'); location.href='showList.php';</script>";
} else {
    echo "<script>alert('商品移除失敗！'); history.back();</script>";
}
?>