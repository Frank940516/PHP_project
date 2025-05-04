<?php
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

// 如果前一頁的路徑包含受保護的資料夾，轉導至首頁
foreach ($protectedFolders as $folder) {
    if (strpos($path, $folder) !== false) {
        header("Location: ../index.php");
        exit();
    }
}

// 否則轉導回前一頁
header("Location: $previousPage");
exit();
?>