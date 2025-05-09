<?php
require('../db.inc'); // 資料庫連線檔案
session_start();

// 檢查是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$userEmail = $_SESSION['user'];

// 查詢使用者資訊
$sqlUser = "SELECT No, Type FROM accounts WHERE Email = ?";
$stmtUser = mysqli_prepare($link, $sqlUser);
mysqli_stmt_bind_param($stmtUser, 's', $userEmail);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);

if (!$user) {
    echo "使用者不存在！";
    exit();
}

$userId = $user['No'];
$userType = $user['Type']; // 確認使用者類型（User 或 Admin）

// 確認商品 ID
$productId = isset($_POST['id']) ? intval($_POST['id']) : (isset($_GET['id']) ? intval($_GET['id']) : null);

if (!$productId) {
    echo "商品 ID 不存在！";
    exit();
}

// 查詢商品資料
if ($userType === 'Admin') {
    // 管理員可以刪除任何商品
    $sqlProduct = "SELECT attachment FROM products WHERE id = ?";
    $stmtProduct = mysqli_prepare($link, $sqlProduct);
    mysqli_stmt_bind_param($stmtProduct, 'i', $productId);
} else {
    // 一般使用者只能刪除自己的商品
    $sqlProduct = "SELECT attachment FROM products WHERE id = ? AND seller_id = ?";
    $stmtProduct = mysqli_prepare($link, $sqlProduct);
    mysqli_stmt_bind_param($stmtProduct, 'ii', $productId, $userId);
}

mysqli_stmt_execute($stmtProduct);
$resultProduct = mysqli_stmt_get_result($stmtProduct);
$product = mysqli_fetch_assoc($resultProduct);

if (!$product) {
    echo "<script>alert('商品不存在或無權限刪除！'); history.back();</script>";
    exit();
}

// 刪除與商品相關的 order_items 記錄
$sqlDeleteOrderItems = "DELETE FROM order_items WHERE product_id = ?";
$stmtDeleteOrderItems = mysqli_prepare($link, $sqlDeleteOrderItems);
mysqli_stmt_bind_param($stmtDeleteOrderItems, 'i', $productId);
mysqli_stmt_execute($stmtDeleteOrderItems);

// 刪除商品附件檔案
if (!empty($product['attachment'])) {
    $filePath = "pic/" . $product['attachment'];
    if (file_exists($filePath)) {
        unlink($filePath); // 刪除檔案
    }
}

// 從資料庫中刪除商品
$sqlDeleteProduct = "DELETE FROM products WHERE id = ?";
$stmtDeleteProduct = mysqli_prepare($link, $sqlDeleteProduct);
mysqli_stmt_bind_param($stmtDeleteProduct, 'i', $productId);
mysqli_stmt_execute($stmtDeleteProduct);

if (mysqli_stmt_affected_rows($stmtDeleteProduct) > 0) {
    if ($userType === 'Admin') {
        echo "<script>alert('商品已成功刪除！'); location.href='../admin/productManagement.php';</script>";
    } else {
        echo "<script>alert('商品已成功刪除！'); location.href='showList.php';</script>";
    }
} else {
    echo "<script>alert('商品刪除失敗！'); history.back();</script>";
}
?>