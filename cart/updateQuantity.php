<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認是否登入
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => '請先登入！']);
    exit();
}

// 接收 JSON 請求
$data = json_decode(file_get_contents('php://input'), true);
$productId = intval($data['product_id']);
$quantity = intval($data['quantity']);

// 查詢使用者 ID
$userEmail = $_SESSION['user'];
$sqlUser = "SELECT No FROM accounts WHERE Email = ?";
$stmtUser = mysqli_prepare($link, $sqlUser);
mysqli_stmt_bind_param($stmtUser, 's', $userEmail);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);

if (!$user) {
    echo json_encode(['success' => false, 'message' => '使用者不存在！']);
    exit();
}

$userId = $user['No'];

// 查詢商品庫存
$sql = "SELECT stock FROM products WHERE id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo json_encode(['success' => false, 'message' => '商品不存在！']);
    exit();
}

if ($quantity > $product['stock']) {
    echo json_encode(['success' => false, 'message' => '數量超過庫存！']);
    exit();
}

// 更新購物車數量
$sqlUpdateCart = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
$stmtUpdateCart = mysqli_prepare($link, $sqlUpdateCart);
mysqli_stmt_bind_param($stmtUpdateCart, 'iii', $quantity, $userId, $productId);
mysqli_stmt_execute($stmtUpdateCart);

echo json_encode(['success' => true]);
?>