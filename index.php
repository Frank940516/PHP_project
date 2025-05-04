<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();
?>
<html>
    <meta charset = "UTF-8">
    <head>
        <title>二手書交易平台-首頁</title>
        <style>
            .top-right-buttons {  /* login & register button */
                position: absolute;
                top: 10px;
                right: 10px;
            }
        </style>
    </head>
    <body>
        <div class="top-right-buttons">
            <?php include('userMenu.php');?>
        </div>
        <div class="left-buttons">
            <input type="button" value="公告" onclick="location.href='announcement/announcement.php'">
            <!-- 新增搜尋欄和搜尋按鈕 -->
            <form action="announcement/search.php" method="GET" style="display: inline;">
                <input type="text" name="query" placeholder="搜尋書籍..." style="padding: 5px; width: 200px; border: 1px solid #ccc; border-radius: 4px;">
                <input type="submit" value="搜尋" style="padding: 5px 10px; background-color: #4CAF50; color: white; border: none; border-radius: 4px; cursor: pointer;">
            </form>
        </div>
    </body>
</html>