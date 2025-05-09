<?php
require('../db.inc'); // 資料庫連線檔案
session_start();

// 檢查是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$userEmail = $_SESSION['user'];

// 查詢使用者 ID 和名稱
$sqlUser = "SELECT No, Name FROM accounts WHERE Email = ?";
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
$userName = $user['Name'];

// 處理表單提交
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'];
    $author = $_POST['author']; // 書籍作者名稱
    $price = $_POST['price'];
    $stock = $_POST['stock'];
    $condition = $_POST['condition'];
    $description = $_POST['description'];
    $category = $_POST['category'];
    $location = $_POST['location']; // 出貨地
    $attachment = '';

    // 插入商品資料（先插入，取得商品 ID）
    $sqlInsert = "INSERT INTO products (name, author, price, stock, `condition`, description, category, location, seller_id) 
                  VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmtInsert = mysqli_prepare($link, $sqlInsert);
    mysqli_stmt_bind_param($stmtInsert, 'ssdissssi', $name, $author, $price, $stock, $condition, $description, $category, $location, $userId);
    mysqli_stmt_execute($stmtInsert);

    if (mysqli_stmt_affected_rows($stmtInsert) > 0) {
        // 取得新增的商品 ID
        $productId = mysqli_insert_id($link);

        // 處理上傳的圖片
        if (isset($_FILES['attachment']) && $_FILES['attachment']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = 'pic/';
            $fileExtension = pathinfo($_FILES['attachment']['name'], PATHINFO_EXTENSION);
            $attachmentName = $userId . '-' . $productId . '.' . $fileExtension; // 用戶ID-商品ID.副檔名
            $attachmentPath = $uploadDir . $attachmentName;

            // 確保目錄存在
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // 移動上傳的檔案
            if (move_uploaded_file($_FILES['attachment']['tmp_name'], $attachmentPath)) {
                // 更新商品的圖片路徑
                $sqlUpdate = "UPDATE products SET attachment = ? WHERE id = ?";
                $stmtUpdate = mysqli_prepare($link, $sqlUpdate);
                mysqli_stmt_bind_param($stmtUpdate, 'si', $attachmentName, $productId);
                mysqli_stmt_execute($stmtUpdate);
            } else {
                echo "<script>alert('圖片上傳失敗！'); history.back();</script>";
                exit();
            }
        }

        // 願望清單通知（比對書名與作者）
        $sqlWish = "SELECT DISTINCT user_id FROM wish_list WHERE book_name = ? AND author = ?";
        $stmtWish = mysqli_prepare($link, $sqlWish);
        mysqli_stmt_bind_param($stmtWish, 'ss', $name, $author);
        mysqli_stmt_execute($stmtWish);
        $resWish = mysqli_stmt_get_result($stmtWish);
        while ($wishUser = mysqli_fetch_assoc($resWish)) {
            // 不通知自己
            if ($wishUser['user_id'] == $userId) continue;

            $notifyMsg = "您想要的書籍「{$name}」已有人上架，快去看看吧！";
            $sqlNotify = "INSERT INTO notifications (user_id, message, product_id) VALUES (?, ?, ?)";
            $stmtNotify = mysqli_prepare($link, $sqlNotify);
            mysqli_stmt_bind_param($stmtNotify, 'isi', $wishUser['user_id'], $notifyMsg, $productId);
            mysqli_stmt_execute($stmtNotify);
        }

        // 新增成功，跳轉到商品管理頁面
        echo "<script>alert('商品新增成功！'); location.href='showList.php';</script>";
    } else {
        echo "<script>alert('商品新增失敗！'); history.back();</script>";
    }
}
?>