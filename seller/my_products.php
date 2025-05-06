<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$seller_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE seller_id = ?");
$stmt->execute([$seller_id]);
$products = $stmt->fetchAll();
?>

<h2>我的上架商品</h2>
<a href="add_product.php">➕ 新增商品</a><br><br>
<table border="1">
    <tr><th>書名</th><th>價格</th><th>狀態</th><th>操作</th></tr>
    <?php foreach ($products as $product): ?>
    <tr>
        <td><?= htmlspecialchars($product['name']) ?></td>
        <td>$<?= $product['price'] ?></td>
        <td><?= $product['status'] ?></td>
        <td>
            <a href="edit_product.php?id=<?= $product['id'] ?>">編輯</a> |
            <a href="delete_product.php?id=<?= $product['id'] ?>" onclick="return confirm('確定刪除？')">刪除</a>
        </td>
    </tr>
    <?php endforeach; ?>
</table>
