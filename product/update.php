<?php
require('../db.inc'); // 資料庫連線檔案
session_start();

// 檢查是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$userEmail = $_SESSION['user'];

// 查詢使用者 ID
$sqlUser = "SELECT No FROM accounts WHERE Email = ?";
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

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $productId = $_POST['id'];
    $name = $_POST['name'];
    $author = $_POST['author']; // 書籍作者名稱
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location']; // 出貨地

    // 更新商品資料
    $sqlUpdate = "UPDATE products SET name = ?, author = ?, price = ?, stock = ?, `condition` = ?, description = ?, category = ?, location = ? WHERE id = ? AND seller_id = ?";
    $stmtUpdate = mysqli_prepare($link, $sqlUpdate);
    mysqli_stmt_bind_param($stmtUpdate, 'ssdissssii', $name, $author, $price, $stock, $condition, $description, $category, $location, $productId, $userId);
    mysqli_stmt_execute($stmtUpdate);

    // 處理圖片更新
    if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
        $uploadDir = 'pic/';
        $fileExtension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
        $attachmentName = $user['Name'] . '-' . $productId . '.' . $fileExtension;
        $attachmentPath = $uploadDir . $attachmentName;

        // 確保目錄存在
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0777, true);
        }

        // 移動上傳的檔案並更新資料庫
        if (move_uploaded_file($_FILES['attachment']['tmp_name'], $attachmentPath)) {
            $sqlUpdateImage = "UPDATE products SET attachment = ? WHERE id = ? AND seller_id = ?";
            $stmtUpdateImage = mysqli_prepare($link, $sqlUpdateImage);
            mysqli_stmt_bind_param($stmtUpdateImage, 'sii', $attachmentName, $productId, $userId);
            mysqli_stmt_execute($stmtUpdateImage);
        }
    }

    echo "<script>alert('商品更新成功！'); location.href='showList.php';</script>";
}
?>