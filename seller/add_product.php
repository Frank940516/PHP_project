<?php
session_start();
include '../config.php';

// 檢查是否為賣家
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = $_POST['name'];
    $author = $_POST['author'];
    $category = $_POST['category'];
    $price = $_POST['price'];
    $description = $_POST['description'];
    $seller_id = $_SESSION['user_id'];

    $stmt = $conn->prepare("INSERT INTO products (seller_id, name, author, category, price, description) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$seller_id, $name, $author, $category, $price, $description]);

    echo "商品上架成功，等待審核！<br><a href='my_products.php'>回到我的商品</a>";
    exit;
}
?>

<h2>上架商品</h2>
<form method="post">
    書名：<input type="text" name="name" required><br>
    作者：<input type="text" name="author" required><br>
    分類：<input type="text" name="category" required><br>
    價格：<input type="number" step="0.01" name="price" required><br>
    說明：<textarea name="description" required></textarea><br>
    <button type="submit">提交商品</button>
</form>
