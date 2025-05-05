<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認使用者是否登入
if (!isset($_SESSION['user'])) {
    echo "請先登入！";
    exit();
}

// 從 Session 中取得使用者 Email
$userEmail = $_SESSION['user'];

// 根據 Email 查詢使用者編號
$sqlGetUserNo = "SELECT No FROM accounts WHERE Email = ?";
$stmtGetUserNo = mysqli_prepare($link, $sqlGetUserNo);
mysqli_stmt_bind_param($stmtGetUserNo, 's', $userEmail);
mysqli_stmt_execute($stmtGetUserNo);
$resultGetUserNo = mysqli_stmt_get_result($stmtGetUserNo);

// 檢查是否找到對應的使用者
if ($rowUser = mysqli_fetch_assoc($resultGetUserNo)) {
    $userId = $rowUser['No'];
} else {
    echo "無效的使用者！";
    exit();
}

// 確認商品 ID 和數量是否存在
if (!isset($_POST['product_id']) || !isset($_POST['quantity'])) {
    echo "商品 ID 或數量未提供！";
    exit();
}

$productId = intval($_POST['product_id']);
$quantity = intval($_POST['quantity']);

// 查詢商品資訊
$sql = "SELECT stock FROM products WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "商品不存在！";
    exit();
}

// 檢查庫存是否足夠
if ($quantity > $product['stock']) {
    echo "購買數量超過庫存！";
    exit();
}

// 將商品加入購物車（假設購物車資料表為 `cart`）
$sqlAddToCart = "INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)
                 ON DUPLICATE KEY UPDATE quantity = quantity + ?";
$stmtAddToCart = mysqli_prepare($link, $sqlAddToCart);
mysqli_stmt_bind_param($stmtAddToCart, 'iiii', $userId, $productId, $quantity, $quantity);
mysqli_stmt_execute($stmtAddToCart);

echo "商品已成功加入購物車！";
header("Location: ../cart/cart.php"); // 跳轉到購物車頁面
exit();
?>