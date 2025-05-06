<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];

$sql = "
SELECT o.id AS order_id, o.quantity, o.total_price, o.order_status, o.created_at,
       p.name AS product_name, u.username AS buyer_name
FROM orders o
JOIN products p ON o.product_id = p.id
JOIN users u ON o.buyer_id = u.id
WHERE p.seller_id = ?
ORDER BY o.created_at DESC
";

$stmt = $conn->prepare($sql);
$stmt->execute([$seller_id]);
$orders = $stmt->fetchAll();
?>

<h2>我的訂單紀錄</h2>
<table border="1">
    <tr>
        <th>訂單編號</th>
        <th>書籍名稱</th>
        <th>購買者</th>
        <th>數量</th>
        <th>總價</th>
        <th>狀態</th>
        <th>下單時間</th>
    </tr>
    <?php foreach ($orders as $order): ?>
    <tr>
        <td><?= $order['order_id'] ?></td>
        <td><?= htmlspecialchars($order['product_name']) ?></td>
        <td><?= htmlspecialchars($order['buyer_name']) ?></td>
        <td><?= $order['quantity'] ?></td>
        <td>$<?= $order['total_price'] ?></td>
        <td><?= $order['order_status'] ?></td>
        <td><?= $order['created_at'] ?></td>
    </tr>
    <?php endforeach; ?>
</table>
