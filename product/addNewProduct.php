<?php
require('../db.inc'); // 資料庫連線檔案
require('../login/authCheck.php'); 
mysqli_set_charset($link, 'utf8');

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

        <label for="author">書籍作者名稱</label>
        <input type="text" id="author" name="author" required>

        <label for="price">價格</label>
        <input type="number" id="price" name="price" step="1" required>

        <label for="stock">庫存</label>
        <input type="number" id="stock" name="stock" required>

        <label for="condition">商品狀況</label>
        <select id="condition" name="condition" required>
            <option value="全新">全新</option>
            <option value="九成新">九成新</option>
            <option value="七成新">七成新</option>
            <option value="五成新">五成新</option>
        </select>

        <label for="category">書籍種類</label>
        <select id="category" name="category" required>
            <option value="文學/小說">文學/小說</option>
            <option value="心理勵志">心理勵志</option>
            <option value="商業/理財">商業/理財</option>
            <option value="藝術/設計">藝術/設計</option>
            <option value="人文/歷史/地理">人文/歷史/地理</option>
            <option value="科學/科普/自然">科學/科普/自然</option>
            <option value="電腦/資訊">電腦/資訊</option>
            <option value="語言學習">語言學習</option>
            <option value="考試用書/教科書">考試用書/教科書</option>
            <option value="童書/繪本">童書/繪本</option>
            <option value="漫畫/輕小說">漫畫/輕小說</option>
            <option value="旅遊/地圖">旅遊/地圖</option>
            <option value="醫療/保健">醫療/保健</option>
            <option value="生活風格/休閒">生活風格/休閒</option>
            <option value="其他">其他</option>
        </select>

        <label for="description">商品描述</label>
        <textarea id="description" name="description" rows="5" required></textarea>

        <label for="location">出貨地</label>
        <input type="text" id="location" name="location" required>

        <label for="attachment">商品圖片</label>
        <input type="file" id="attachment" name="attachment" accept="image/*" required>

        <button type="submit">新增商品</button>
    </form>
</body>
</html>