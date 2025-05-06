<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認是否登入
if (!isset($_SESSION['user'])) {
    echo "請先登入！";
    exit();
}

// 確認是否有提供商品 ID
if (!isset($_GET['product_id'])) {
    echo "未提供商品 ID！";
    exit();
}

$productId = intval($_GET['product_id']);

// 從 Session 中取得使用者 Email
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

// 查詢購物車中該商品的總數量
$sqlCart = "SELECT SUM(quantity) AS total_quantity FROM cart WHERE user_id = ? AND product_id = ?";
$stmtCart = mysqli_prepare($link, $sqlCart);
mysqli_stmt_bind_param($stmtCart, 'ii', $userId, $productId);
mysqli_stmt_execute($stmtCart);
$resultCart = mysqli_stmt_get_result($stmtCart);
$cartItem = mysqli_fetch_assoc($resultCart);

if (!$cartItem || $cartItem['total_quantity'] == 0) {
    echo "購物車中沒有該商品！";
    exit();
}

$totalQuantity = $cartItem['total_quantity'];

// 刪除購物車中的商品
$sqlDeleteCart = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
$stmtDeleteCart = mysqli_prepare($link, $sqlDeleteCart);
mysqli_stmt_bind_param($stmtDeleteCart, 'ii', $userId, $productId);
mysqli_stmt_execute($stmtDeleteCart);

// 將商品庫存加回去
$sqlUpdateStock = "UPDATE products SET stock = stock + ? WHERE id = ?";
$stmtUpdateStock = mysqli_prepare($link, $sqlUpdateStock);
mysqli_stmt_bind_param($stmtUpdateStock, 'ii', $totalQuantity, $productId);
mysqli_stmt_execute($stmtUpdateStock);

echo "商品已成功刪除！";
header("Location: cart.php"); // 刪除後跳轉回購物車頁面
exit();
?>