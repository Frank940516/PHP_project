<?php
session_start();
include '../config.php';
$id = $_SESSION['user_id'];

$stmt = $conn->prepare("SELECT c.*, p.name FROM cart c JOIN products p ON c.product_id = p.id WHERE c.user_id = ?");
$stmt->execute([$id]);
$items = $stmt->fetchAll();

echo "<h3>購物車</h3>";
$total = 0;
foreach ($items as $item) {
    $subtotal = $item['quantity'] * $item['price'];
    $total += $subtotal;
    echo "{$item['name']} x {$item['quantity']} = $subtotal<br>";
}
echo "總金額：$total 元<br>";
echo "<a href='checkout.php'>前往結帳</a>";
