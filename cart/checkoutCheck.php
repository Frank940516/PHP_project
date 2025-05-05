<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認是否登入
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

// 檢查是否是單一商品結帳
if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
    $products = [
        [
            'product_id' => intval($_POST['product_id']),
            'quantity' => intval($_POST['quantity']),
        ]
    ];
} elseif (isset($_POST['products']) && is_array($_POST['products'])) {
    $products = $_POST['products'];
} else {
    echo "缺少必要的參數！";
    exit();
}

$totalAmount = 0; // 訂單總金額
$orderItems = []; // 訂單商品明細

foreach ($products as $productData) {
    $productId = intval($productData['product_id']);
    $quantity = intval($productData['quantity']);

    // 查詢商品庫存和價格
    $sqlProduct = "SELECT stock, price FROM products WHERE id = ?";
    $stmtProduct = mysqli_prepare($link, $sqlProduct);
    mysqli_stmt_bind_param($stmtProduct, 'i', $productId);
    mysqli_stmt_execute($stmtProduct);
    $resultProduct = mysqli_stmt_get_result($stmtProduct);
    $product = mysqli_fetch_assoc($resultProduct);

    if (!$product) {
        echo "商品 ID {$productId} 不存在！";
        exit();
    }

    if ($quantity > $product['stock']) {
        echo "商品 ID {$productId} 庫存不足，無法結帳！";
        exit();
    }

    // 計算小計並累加到總金額
    $subtotal = $product['price'] * $quantity;
    $totalAmount += $subtotal;

    // 更新商品庫存
    $newStock = $product['stock'] - $quantity;
    $sqlUpdateStock = "UPDATE products SET stock = ? WHERE id = ?";
    $stmtUpdateStock = mysqli_prepare($link, $sqlUpdateStock);
    mysqli_stmt_bind_param($stmtUpdateStock, 'ii', $newStock, $productId);
    mysqli_stmt_execute($stmtUpdateStock);

    // 添加到訂單商品明細
    $orderItems[] = [
        'product_id' => $productId,
        'quantity' => $quantity,
        'price' => $product['price'],
        'subtotal' => $subtotal,
    ];
}

// 插入訂單記錄
$sqlInsertOrder = "INSERT INTO orders (user_id, total_amount) VALUES (?, ?)";
$stmtInsertOrder = mysqli_prepare($link, $sqlInsertOrder);
mysqli_stmt_bind_param($stmtInsertOrder, 'id', $userId, $totalAmount);
mysqli_stmt_execute($stmtInsertOrder);
$orderId = mysqli_insert_id($link);

// 插入訂單商品明細
$sqlInsertOrderItem = "INSERT INTO order_items (order_id, product_id, quantity, price, subtotal) VALUES (?, ?, ?, ?, ?)";
$stmtInsertOrderItem = mysqli_prepare($link, $sqlInsertOrderItem);

foreach ($orderItems as $item) {
    mysqli_stmt_bind_param(
        $stmtInsertOrderItem,
        'iiidd',
        $orderId,
        $item['product_id'],
        $item['quantity'],
        $item['price'],
        $item['subtotal']
    );
    mysqli_stmt_execute($stmtInsertOrderItem);
}

// 從購物車中移除已結帳的商品
$sqlDeleteCart = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
$stmtDeleteCart = mysqli_prepare($link, $sqlDeleteCart);

foreach ($orderItems as $item) {
    mysqli_stmt_bind_param($stmtDeleteCart, 'ii', $userId, $item['product_id']);
    mysqli_stmt_execute($stmtDeleteCart);
}

// 返回購物車頁面
header("Location: cart.php");
exit();
?>