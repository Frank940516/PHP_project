<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("SELECT * FROM products WHERE id = ? AND seller_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
$product = $stmt->fetch();

if (!$product) {
    echo "商品不存在或您無權限修改";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description'];

    $stmt = $conn->prepare("UPDATE products SET name=?, author=?, category=?, price=?, description=?, status='pending' WHERE id=? AND seller_id=?");
    $stmt->execute([$name, $author, $category, $price, $description, $id, $_SESSION['user_id']]);
    echo "修改成功！<a href='my_products.php'>返回</a>";
    exit;
}
?>

<h2>編輯商品</h2>
<form method="post">
    書名：<input type="text" name="name" value="<?= htmlspecialchars($product['name']) ?>" required><br>
    作者：<input type="text" name="author" value="<?= htmlspecialchars($product['author']) ?>" required><br>
    分類：<input type="text" name="category" value="<?= htmlspecialchars($product['category']) ?>" required><br>
    價格：<input type="number" name="price" step="0.01" value="<?= $product['price'] ?>" required><br>
    說明：<textarea name="description" required><?= htmlspecialchars($product['description']) ?></textarea><br>
    <button type="submit">更新商品</button>
</form>
