<?php
    require('../db.inc');
    require('../parsedown/Parsedown.php'); // 引入 Parsedown 類
    mysqli_set_charset($link, 'utf8');
    date_default_timezone_set('Asia/Taipei');
    session_start();

    $announcementNo = isset($_GET['No']) ? intval($_GET['No']) : 0;

    // Show the announcement details
    $sql = "SELECT announcement.Title, announcement.Content, announcement.Date, accounts.Name AS PublisherName
            FROM announcement
            JOIN accounts ON announcement.Publisher = accounts.No
            WHERE announcement.No = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $announcementNo);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $announcement = mysqli_fetch_assoc($result);

    if (!$announcement) { // No announcement found
        echo "<h1>公告不存在</h1>";
        header("Location: announcement.php");
        exit();
    }

    $pageTitle = htmlspecialchars($announcement['Title']);
    $parsedown = new Parsedown(); // 初始化 Parsedown
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title><?php echo $pageTitle; ?> - 公告細節</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f9;
                margin: 0;
                padding: 0;
            }
            .container {
                max-width: 800px;
                margin: 50px auto;
                background: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            h1 {
                font-size: 24px;
                color: #333;
                border-bottom: 2px solid #4CAF50;
                padding-bottom: 10px;
            }
            p {
                font-size: 16px;
                color: #555;
                line-height: 1.6;
            }
            .meta {
                margin-bottom: 20px;
                font-size: 14px;
                color: #888;
            }
            .meta strong {
                color: #333;
            }
            .content {
                white-space: normal;
                background: #f9f9f9;
                padding: 15px;
                border-left: 4px solid #4CAF50;
                border-radius: 4px;
            }
            .back-button, .edit-button {
                display: inline-block;
                margin-top: 20px;
                padding: 10px 15px;
                font-size: 14px;
                color: #fff;
                background-color: #4CAF50;
                text-decoration: none;
                border-radius: 4px;
                transition: background-color 0.3s ease;
            }
            .back-button:hover, .edit-button:hover {
                background-color: #45a049;
            }
            .top-right-buttons {
                position: absolute;
                top: 10px;
                right: 10px;
            }
            .top-right-buttons input {
                margin-left: 10px;
                padding: 5px 10px;
                font-size: 14px;
                cursor: pointer;
            }
        </style>
    </head>
    <body>
        <!-- 登入/登出按鈕 -->
        <div class="top-right-buttons">
            <?php include('../userMenu.php'); ?>
        </div>

        <div class="container">
            <h1><?php echo $pageTitle; ?></h1>
            <div class="meta">
                <p><strong>發布者：</strong><?php echo htmlspecialchars($announcement['PublisherName']); ?></p>
                <p><strong>公告日期：</strong><?php echo htmlspecialchars($announcement['Date']); ?></p>
            </div>
            <div class="content">
                <?php 
                // 使用 nl2br 將換行符號轉換為 <br>，然後再用 Parsedown 處理 Markdown
                echo $parsedown->text(nl2br(htmlspecialchars($announcement['Content']))); 
                ?>
            </div>
            <a href="announcement.php" class="back-button">返回公告列表</a>
            <?php
                // 如果是管理員，顯示編輯按鈕
                if (isset($_SESSION["type"]) && $_SESSION["type"] === "Admin") {
                    echo "<a href='edit.php?No=" . htmlspecialchars($announcementNo) . "' class='edit-button'>編輯公告</a>";
                }
            ?>
        </div>
    </body>
</html>