<?php
session_start();
include '../config.php';
$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT * FROM orders WHERE buyer_id = ?");
$stmt->execute([$id]);
$orders = $stmt->fetchAll();

foreach ($orders as $order) {
    echo "<a href='order_detail.php?id={$order['id']}'>訂單編號：{$order['id']}</a><br>";
}
