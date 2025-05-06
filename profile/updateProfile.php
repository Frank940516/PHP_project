<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 檢查是否登入
if (!isset($_SESSION["user"])) {
    header("Location: ../login/login.php");
    exit();
}

// 獲取表單資料
$name = trim($_POST['name']);
if (mb_strlen($name, 'UTF-8') > 20) {
    echo "使用者名稱長度不能超過 20 字！";
    exit();
}
$email = $_SESSION["user"]; // Email 不允許修改

// 驗證資料
if (empty($name)) {
    header("Location: profile.php?error=empty_name");
    exit();
}

// 更新資料庫
$sql = "UPDATE accounts SET Name = ? WHERE Email = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $name, $email);

if (mysqli_stmt_execute($stmt)) {
    // 更新成功，將新的名稱同步到 SESSION
    $_SESSION["name"] = $name;

    // 返回個人資料頁面
    header("Location: profile.php?success=1");
    exit();
} else {
    // 更新失敗，顯示錯誤訊息
    echo "更新失敗：" . mysqli_error($link);
}
?>