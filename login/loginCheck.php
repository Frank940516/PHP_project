<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 驗證 CSRF Token
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('CSRF 驗證失敗');
}

// 接收輸入
$userEmail = trim($_POST["email"]);
$userPassword = $_POST["password"];
$redirectUrl = isset($_POST['redirect']) && !empty($_POST['redirect']) ? $_POST['redirect'] : '../index.php';

// 驗證輸入長度
if (strlen($userEmail) > 255 || strlen($userPassword) > 128) {
    header("Location: login.php?error=invalid_input");
    exit();
}

// 查詢帳號
$sql = "SELECT * FROM accounts WHERE Email=?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 's', $userEmail);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result); // 帳號存在

    // 檢查使用者是否被封鎖
    if ($row["Status"] === "blocked") {
        $blockReason = isset($row["block_reason"]) ? urlencode($row["block_reason"]) : "無具體原因";
        header("Location: login.php?error=blocked&reason=$blockReason");
        exit();
    }

    // 檢查密碼是否正確
    if (password_verify($userPassword, $row["Password"])) {
        // 登入成功，重置登入失敗次數
        $sqlResetAttempts = "UPDATE accounts SET login_attempts = 0 WHERE Email=?";
        $stmtReset = mysqli_prepare($link, $sqlResetAttempts);
        mysqli_stmt_bind_param($stmtReset, 's', $userEmail);
        mysqli_stmt_execute($stmtReset);

        // 設定 Session
        $_SESSION["user"] = $row["Email"];
        $_SESSION["name"] = $row["Name"];
        $_SESSION["type"] = $row["Type"];
        $_SESSION["user_id"] = $row["No"];

        // 驗證 $redirectUrl 是否安全
        if (filter_var($redirectUrl, FILTER_VALIDATE_URL) || strpos($redirectUrl, '/') === 0) {
            header("Location: $redirectUrl");
        } else {
            header("Location: ../index.php");
        }
        exit();
    } else {
        // 密碼錯誤，增加登入失敗次數
        $sqlUpdateAttempts = "UPDATE accounts SET login_attempts = login_attempts + 1 WHERE Email=?";
        $stmtUpdate = mysqli_prepare($link, $sqlUpdateAttempts);
        mysqli_stmt_bind_param($stmtUpdate, 's', $userEmail);
        mysqli_stmt_execute($stmtUpdate);

        header("Location: login.php?error=invalid_credentials");
        exit();
    }
} else {
    // 帳號不存在
    header("Location: login.php?error=invalid_credentials");
    exit();
}
?>