<?php
require('../db.inc');
session_start(); // 確保啟用 session
mysqli_set_charset($link, 'utf8');

// 確認是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// 確認是否為管理員
$userEmail = $_SESSION['user'];
$sqlUserType = "SELECT Type FROM accounts WHERE Email = ?";
$stmtUserType = mysqli_prepare($link, $sqlUserType);
mysqli_stmt_bind_param($stmtUserType, 's', $userEmail);
mysqli_stmt_execute($stmtUserType);
$resultUserType = mysqli_stmt_get_result($stmtUserType);
$user = mysqli_fetch_assoc($resultUserType);

if (!$user || $user['Type'] !== 'Admin') {
    die("您沒有權限執行此操作！");
}

// 處理刪除請求
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $couponId = intval($_POST['id']);
    $sqlDelete = "DELETE FROM coupons WHERE id = ?";
    $stmtDelete = mysqli_prepare($link, $sqlDelete);
    mysqli_stmt_bind_param($stmtDelete, 'i', $couponId);

    if (mysqli_stmt_execute($stmtDelete)) {
        header("Location: couponList.php?message=刪除成功");
        exit();
    } else {
        die("刪除失敗：" . mysqli_error($link));
    }
} else {
    die("無效的請求！");
}
?>