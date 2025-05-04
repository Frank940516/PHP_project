<?php
require('../db.inc'); // 資料庫連線檔案
require('../userMenu.php'); // 引入使用者功能清單
mysqli_set_charset($link, 'utf8');
session_start();

// 檢查是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增商品</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-home-button input {
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
        }
        form {
            max-width: 600px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <!-- Top bar with back home button and user menu -->
    <div class="top-bar">
        <div class="back-home-button">
            <input type="button" value="返回首頁" onclick="location.href='../index.php'">
        </div>
        <?php require('../userMenu.php'); ?>
    </div>

    <h1>新增商品</h1>
    <form method="POST" action="addCheck.php" enctype="multipart/form-data">
        <label for="name">商品名稱</label>
        <input type="text" id="name" name="name" required>

        <label for="price">價格</label>
        <input type="number" id="price" name="price" step="0.01" required>

        <label for="stock">庫存</label>
        <input type="number" id="stock" name="stock" required>

        <label for="condition">商品狀況</label>
        <select id="condition" name="condition" required>
            <option value="全新">全新</option>
            <option value="九成新">九成新</option>
            <option value="七成新">七成新</option>
            <option value="五成新">五成新</option>
        </select>

        <label for="description">商品描述</label>
        <textarea id="description" name="description" rows="5" required></textarea>

        <label for="attachment">商品圖片</label>
        <input type="file" id="attachment" name="attachment" accept="image/*">

        <button type="submit">新增商品</button>
    </form>
</body>
</html>