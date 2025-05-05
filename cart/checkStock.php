<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認是否登入
if (!isset($_SESSION['user'])) {
    echo json_encode(['success' => false, 'message' => '請先登入！']);
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
    echo json_encode(['success' => false, 'message' => '使用者不存在！']);
    exit();
}

$userId = $user['No'];

// 查詢購物車內容
$sqlCart = "SELECT c.product_id, c.quantity, p.stock, p.name 
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?";
$stmtCart = mysqli_prepare($link, $sqlCart);
mysqli_stmt_bind_param($stmtCart, 'i', $userId);
mysqli_stmt_execute($stmtCart);
$resultCart = mysqli_stmt_get_result($stmtCart);

while ($row = mysqli_fetch_assoc($resultCart)) {
    if ($row['quantity'] > $row['stock']) {
        echo json_encode(['success' => false, 'message' => "商品 '{$row['name']}' 庫存不足！"]);
        exit();
    }
}

echo json_encode(['success' => true]);
?>