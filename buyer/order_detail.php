<?php
session_start();
include '../config.php';
$order_id = $_GET['id'];

$stmt = $conn->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

echo "<h3>訂單明細</h3>";
foreach ($items as $item) {
    echo "書籍：{$item['product_name']} 數量：{$item['quantity']}<br>";
}
