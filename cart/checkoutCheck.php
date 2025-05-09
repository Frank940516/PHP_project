<!-- checkoutCheck.php -->
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

// 檢查商品與數量
if (isset($_POST['product_id'], $_POST['quantity'])) {
    $products = [[
        'product_id' => intval($_POST['product_id']),
        'quantity'   => intval($_POST['quantity']),
    ]];
} else {
    echo "缺少必要的參數！";
    exit();
}

// 檢查付款方式
if (!isset($_POST['payment_method'])) {
    echo "請選擇付款方式！";
    exit();
}
$paymentMethod = $_POST['payment_method'];

// 計算總金額與檢查庫存
$totalAmount = 0;
$orderItems  = [];
foreach ($products as $p) {
    // 查詢庫存與單價
    $sqlP = "SELECT stock, price FROM products WHERE id = ?";
    $stmtP = mysqli_prepare($link, $sqlP);
    mysqli_stmt_bind_param($stmtP, 'i', $p['product_id']);
    mysqli_stmt_execute($stmtP);
    $resP = mysqli_stmt_get_result($stmtP);
    $rowP = mysqli_fetch_assoc($resP);
    if (!$rowP || $p['quantity'] > $rowP['stock']) {
        echo "商品 ID {$p['product_id']} 不存在或庫存不足！";
        exit();
    }

    $subtotal = $rowP['price'] * $p['quantity'];
    $totalAmount += $subtotal;
    $orderItems[] = [
        'product_id' => $p['product_id'],
        'quantity'   => $p['quantity'],
        'price'      => $rowP['price'],
        'subtotal'   => $subtotal,
    ];

    // 扣除庫存
    $newStock = $rowP['stock'] - $p['quantity'];
    $sqlUp = "UPDATE products SET stock = ? WHERE id = ?";
    $stmtUp = mysqli_prepare($link, $sqlUp);
    mysqli_stmt_bind_param($stmtUp, 'ii', $newStock, $p['product_id']);
    mysqli_stmt_execute($stmtUp);
}

// 處理優惠券（若有）
$couponCode     = null;
$couponDiscount = 0;
if (!empty($_POST['selected_coupon'])) {
    $code = trim($_POST['selected_coupon']);
    $sqlC = "
        SELECT c.id, c.discount, c.discount_type
          FROM coupons c
          JOIN user_coupons uc ON c.id = uc.coupon_id
         WHERE uc.user_id = ? 
           AND c.code = ?
           AND uc.is_used = 0
           AND c.is_active = 1
           AND c.expiration_date >= CURDATE()
           AND c.start_date <= CURDATE()
    ";
    $stmtC = mysqli_prepare($link, $sqlC);
    mysqli_stmt_bind_param($stmtC, 'is', $userId, $code);
    mysqli_stmt_execute($stmtC);
    $resC = mysqli_stmt_get_result($stmtC);
    $coupon = mysqli_fetch_assoc($resC);

    if ($coupon) {
        // 計算折扣金額
        if ($coupon['discount_type'] === 'percentage') {
            $couponDiscount = $totalAmount * ($coupon['discount'] / 100);
        } else {
            $couponDiscount = $coupon['discount'];
        }
        $couponDiscount = min($couponDiscount, $totalAmount);
        $totalAmount -= $couponDiscount;
        $couponCode = $code;

        // 更新優惠券為已使用
        $sqlU = "UPDATE user_coupons SET is_used = 1 WHERE user_id = ? AND coupon_id = ?";
        $stmtU = mysqli_prepare($link, $sqlU);
        mysqli_stmt_bind_param($stmtU, 'ii', $userId, $coupon['id']);
        mysqli_stmt_execute($stmtU);
    }
}

// 寫入 orders
$sqlO = "
    INSERT INTO orders
        (user_id, total_amount, payment_method, coupon_code, coupon_discount)
    VALUES (?, ?, ?, ?, ?)
";
$stmtO = mysqli_prepare($link, $sqlO);
mysqli_stmt_bind_param($stmtO, 'idssd', $userId, $totalAmount, $paymentMethod, $couponCode, $couponDiscount);
mysqli_stmt_execute($stmtO);
$orderId = mysqli_insert_id($link);

// 寫入 order_items
$sqlI = "
    INSERT INTO order_items
        (order_id, product_id, quantity, price, subtotal)
    VALUES (?, ?, ?, ?, ?)
";
$stmtI = mysqli_prepare($link, $sqlI);
foreach ($orderItems as $it) {
    mysqli_stmt_bind_param(
        $stmtI,
        'iiidd',
        $orderId,
        $it['product_id'],
        $it['quantity'],
        $it['price'],
        $it['subtotal']
    );
    mysqli_stmt_execute($stmtI);

    // 查詢商品的賣家
    $sqlSeller = "SELECT seller_id, name FROM products WHERE id = ?";
    $stmtSeller = mysqli_prepare($link, $sqlSeller);
    mysqli_stmt_bind_param($stmtSeller, 'i', $it['product_id']);
    mysqli_stmt_execute($stmtSeller);
    $resultSeller = mysqli_stmt_get_result($stmtSeller);
    $seller = mysqli_fetch_assoc($resultSeller);

    if ($seller) {
        $buyerName = $_SESSION['name'];
        $productName = $seller['name'];
        $sellerId = $seller['seller_id'];

        // 存純文字
        $notificationMessage = "買家 {$buyerName} 購買了您的商品 {$productName}，數量：{$it['quantity']}。";

        $sqlNotification = "INSERT INTO notifications (user_id, message) VALUES (?, ?)";
        $stmtNotification = mysqli_prepare($link, $sqlNotification);
        mysqli_stmt_bind_param($stmtNotification, 'is', $sellerId, $notificationMessage);
        mysqli_stmt_execute($stmtNotification);
    }
}

// 清空購物車
$sqlD = "DELETE FROM cart WHERE user_id = ? AND product_id = ?";
$stmtD = mysqli_prepare($link, $sqlD);
foreach ($orderItems as $it) {
    mysqli_stmt_bind_param($stmtD, 'ii', $userId, $it['product_id']);
    mysqli_stmt_execute($stmtD);
}

// 完成，導回購物車
header("Location: cart.php");
exit();
