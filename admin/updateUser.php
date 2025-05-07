<?php
require('../db.inc');
session_start();

// 檢查是否已登入
if (!isset($_SESSION['user']) || $_SESSION['type'] !== 'Admin') {
    echo "您無權執行此操作！";
    exit();
}

// 獲取 POST 資料
$userId = $_POST['user_id'];
$action = $_POST['action'];
$blockReason = isset($_POST['block_reason']) ? $_POST['block_reason'] : null;

// 根據操作執行對應的 SQL
if ($action === 'promote') {
    $sql = "UPDATE accounts SET Type = 'Admin' WHERE No = ?";
} elseif ($action === 'block') {
    // 封鎖使用者並刪除其所有商品
    $sql = "UPDATE accounts SET Status = 'blocked', block_reason = ? WHERE No = ?";
    $deleteProductsSql = "DELETE FROM products WHERE seller_id = ?";
} elseif ($action === 'demote') {
    $sql = "UPDATE accounts SET Type = 'User' WHERE No = ?";
} elseif ($action === 'unblock') {
    $sql = "UPDATE accounts SET Status = 'active', block_reason = NULL WHERE No = ?";
} else {
    echo "無效的操作！";
    exit();
}

// 執行主要操作
if ($action === 'block') {
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'si', $blockReason, $userId);
} else {
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
}

if (mysqli_stmt_execute($stmt)) {
    // 如果是封鎖操作，刪除該使用者的所有商品
    if ($action === 'block') {
        $deleteStmt = mysqli_prepare($link, $deleteProductsSql);
        mysqli_stmt_bind_param($deleteStmt, 'i', $userId);
        mysqli_stmt_execute($deleteStmt);
    }

    // 設定成功訊息
    $successMessage = '';
    if ($action === 'promote') {
        $successMessage = '升級成功！';
    } elseif ($action === 'demote') {
        $successMessage = '降級成功！';
    } elseif ($action === 'block') {
        $successMessage = '封鎖成功，並刪除了該使用者的所有商品！';
    } elseif ($action === 'unblock') {
        $successMessage = '解封成功！';
    }
    header("Location: userManagement.php?success=" . urlencode($successMessage));
    exit();
} else {
    echo "操作失敗：" . mysqli_error($link);
}
?>