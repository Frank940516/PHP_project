<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
date_default_timezone_set('Asia/Taipei');
session_start();

// 檢查是否為管理員
if (!isset($_SESSION["type"]) || $_SESSION["type"] !== "Admin") {
    header("Location: ../index.php");
    exit();
}

// 獲取表單資料
$announcementNo = isset($_POST['No']) ? intval($_POST['No']) : 0;
$title = isset($_POST['title']) ? trim($_POST['title']) : '';
$content = isset($_POST['content']) ? trim($_POST['content']) : '';
$date = date('Y-m-d H:i:s'); // 更新公告時間

// 驗證標題長度
$title = trim($_POST['title']);
if (mb_strlen($title, 'UTF-8') > 50) {
    echo "標題長度不能超過 50 字！";
    exit();
}

// 確保 Publisher 是有效的 accounts.No
$userEmail = $_SESSION['user'];
$sqlGetPublisher = "SELECT No FROM accounts WHERE Email = ?";
$stmtGetPublisher = mysqli_prepare($link, $sqlGetPublisher);
mysqli_stmt_bind_param($stmtGetPublisher, 's', $userEmail);
mysqli_stmt_execute($stmtGetPublisher);
$resultGetPublisher = mysqli_stmt_get_result($stmtGetPublisher);
$publisherRow = mysqli_fetch_assoc($resultGetPublisher);

if (!$publisherRow) {
    // 如果找不到對應的使用者，返回錯誤
    header("Location: edit.php?No=$announcementNo&error=invalid_user");
    exit();
}

$publisher = $publisherRow['No']; // 獲取 Publisher 的 No

// 驗證資料
if (empty($title) || empty($content)) {
    header("Location: edit.php?No=$announcementNo&error=empty_fields");
    exit();
}

// 更新資料庫
$sql = "UPDATE announcement SET Title = ?, Content = ?, Date = ?, Publisher = ? WHERE No = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'sssii', $title, $content, $date, $publisher, $announcementNo);

if (mysqli_stmt_execute($stmt)) {
    // 更新成功，返回公告細節頁面
    header("Location: detail.php?No=$announcementNo");
    exit();
} else {
    // 更新失敗，顯示錯誤訊息
    echo "更新公告失敗：" . mysqli_error($link);
}
?>