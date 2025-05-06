<?php
session_start();
include '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'seller') {
    header("Location: ../login.php");
    exit;
}

$id = $_GET['id'];
$stmt = $conn->prepare("DELETE FROM products WHERE id = ? AND seller_id = ?");
$stmt->execute([$id, $_SESSION['user_id']]);
header("Location: my_products.php");
exit;
