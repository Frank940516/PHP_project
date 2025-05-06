<?php
session_start();
include '../config.php';
$user_id = $_SESSION['user_id'];

// 模擬下單流程
$conn->beginTransaction();
$conn->exec("INSERT INTO orders (buyer_id, order_date) VALUES ($user_id, NOW())");
$order_id = $conn->lastInsertId();

// 加入 order_items
$stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ?");
$stmt->execute([$user_id]);
$items = $stmt->fetchAll();

foreach ($items as $item) {
    $conn->prepare("INSERT INTO order_items (order_id, product_id, product_name, quantity, price)
                    VALUES (?, ?, ?, ?, ?)")
         ->execute([$order_id, $item['product_id'], $item['product_name'], $item['quantity'], $item['price']]);
}

// 清空購物車
$conn->prepare("DELETE FROM cart WHERE user_id = ?")->execute([$user_id]);
$conn->commit();

echo "下單成功！<a href='orders.php'>查看訂單</a>";
