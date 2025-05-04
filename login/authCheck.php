<?php
session_start();

// 如果使用者未登入，跳轉到登入頁面，並附加當前頁面的 URL 作為 redirect 參數
if (!isset($_SESSION["user"])) {
    $currentUrl = urlencode($_SERVER['REQUEST_URI']); // 將當前頁面 URL 編碼
    header("Location: login/login.php?redirect=$currentUrl");
    exit();
}
?>