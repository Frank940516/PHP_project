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
$title = isset($_POST['title']) ? mysqli_real_escape_string($link, $_POST['title']) : '';
$content = isset($_POST['content']) ? mysqli_real_escape_string($link, $_POST['content']) : '';
$userEmail = isset($_SESSION['user']) ? $_SESSION['user'] : ''; // 使用目前登入的使用者 Email
$date = date('Y-m-d H:i:s'); // 取得目前時間

// 檢查資料是否完整
if (empty($title) || empty($content) || empty($userEmail)) {
    header("Location: createNewAnnouncement.php?error=missing_data");
    exit();
}

// 查詢 Publisher 的 No
$sqlGetPublisher = "SELECT No FROM accounts WHERE Email = '$userEmail'";
$result = mysqli_query($link, $sqlGetPublisher);
if ($result && mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    $publisherNo = $row['No'];
} else {
    // 如果找不到對應的使用者，返回錯誤
    header("Location: createNewAnnouncement.php?error=invalid_user");
    exit();
}

// 插入資料到資料庫
$sql = "INSERT INTO announcement (Title, Content, Date, Publisher) VALUES ('$title', '$content', '$date', '$publisherNo')";
if (mysqli_query($link, $sql)) {
    // 插入成功，重新導向到公告列表頁面
    header("Location: announcement.php?success=1");
    exit();
} else {
    // 插入失敗，顯示錯誤訊息
    echo "Error: " . mysqli_error($link);
}
?>