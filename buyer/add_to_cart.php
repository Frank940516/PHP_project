<?php
session_start();
include '../config.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $product_id = $_POST['product_id'];
    $quantity = $_POST['quantity'];
    $user_id = $_SESSION['user_id'];

    // 檢查是否已有加入
    $stmt = $conn->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
    $stmt->execute([$user_id, $product_id]);

    if ($stmt->rowCount() > 0) {
        $conn->prepare("UPDATE cart SET quantity = quantity + ? WHERE user_id = ? AND product_id = ?")
             ->execute([$quantity, $user_id, $product_id]);
    } else {
        $conn->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)")
             ->execute([$user_id, $product_id, $quantity]);
    }

    header("Location: cart.php");
}
