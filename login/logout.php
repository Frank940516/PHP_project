<?php
// filepath: c:\xampp\htdocs\login\logout.php
session_start();
session_unset();
session_destroy();

// 檢查是否來自需要登入的頁面
$previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';

// 定義需要登入的資料夾
$protectedFolders = [
    '/product/',
    '/profile/',
    '/cart/',
];

// 確保 $previousPage 是安全的 URL
$path = parse_url($previousPage, PHP_URL_PATH);
$isProtected = false;

foreach ($protectedFolders as $folder) {
    if (strpos($path, $folder) === 0) {
        $isProtected = true;
        break;
    }
}

// 如果前一頁是受保護的頁面，轉導至首頁
if ($isProtected) {
    header("Location: ../index.php");
} else {
    // 否則轉導回前一頁
    header("Location: $previousPage");
}
exit();
?>