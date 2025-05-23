<?php
require('../db.inc');
require('../parsedown/Parsedown.php'); // 引入 Parsedown 類
mysqli_set_charset($link, 'utf8');
date_default_timezone_set('Asia/Taipei');
session_start();
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>二手書交易平台-公告</title>
        <style>
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            tr:hover {
                background-color: #f5f5f5;
            }
            .date-column {
                width: 20%; /* 縮短公告時間列的寬度 */
            }
            .title-column {
                width: 60%; /* 增加公告標題列的寬度 */
                font-size: 18px; /* 增加字體大小 */
                padding: 12px; /* 增加內邊距 */
            }
            .publisher-column {
                width: 20%; /* 保持發布者列的寬度 */
            }
            .top-right-buttons {
                position: absolute;
                top: 10px;
                right: 10px;
            }
            .top-right-buttons input {
                margin: 5px;
            }
            .header-container {
                display: flex;
                align-items: center;
                margin-bottom: 20px;
            }
            .admin-button {
                margin-left: 10px; /* 按鈕與標題之間的間距 */
            }
            .admin-button input {
                padding: 5px 10px;
                font-size: 14px;
                cursor: pointer;
            }
            .back-home-button input{
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
            margin-right: 10px; /* 與標題之間的間距 */
            }
            .search-container {
                margin-bottom: 20px;
                display: flex;
                justify-content: flex-start;
                align-items: center;
            }
            .search-input {
                padding: 8px;
                font-size: 14px;
                width: 300px;
                border: 1px solid #ddd;
                border-radius: 4px;
                margin-right: 10px;
            }
            .search-button {
                padding: 8px 15px;
                font-size: 14px;
                color: #fff;
                background-color: #4CAF50;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                transition: background-color 0.3s ease;
            }
            .search-button:hover {
                background-color: #45a049;
            }
        </style>
    </head>
    <body>
        <!-- login & register button -->
        <div class="top-right-buttons">
            <?php include('../userMenu.php'); ?>
        </div>

        <!-- 公告列表標題和新增公告按鈕 -->
        <div class="header-container">
        <div class="back-home-button">
            <input type="button" value="返回首頁" onclick="location.href='../index.php'">
        </div>
            <h1>公告列表</h1>
            <?php
            if (isset($_SESSION["type"]) && $_SESSION["type"] === "Admin") {
                echo "<div class='admin-button'>";
                echo "<input type='button' value='新增公告' onclick=\"location.href='createNewAnnouncement.php'\">";
                echo "</div>";
            }
            ?>
        </div>

        <!-- search bar -->
        <div class="search-container">
            <form action="search.php" method="GET">
                <input type="text" name="query" placeholder="搜尋公告..." class="search-input">
                <button type="submit" class="search-button">搜尋</button>
            </form>
        </div>

        <!-- announcement area -->
        <?php
        // 查詢公告資料
        $sql = "SELECT announcement.No, announcement.Title, announcement.Date, accounts.Name AS PublisherName
                FROM announcement
                JOIN accounts ON announcement.Publisher = accounts.No
                ORDER BY Date DESC";
        $result = mysqli_query($link, $sql);

        // 檢查查詢是否成功
        if (!$result) {
            echo "<p>查詢公告資料時發生錯誤：" . mysqli_error($link) . "</p>";
            exit();
        }
        ?>
        <table>
            <thead>
                <tr>
                    <th class="date-column">公告時間</th>
                    <th class="title-column">公告標題</th>
                    <th class="publisher-column">發布者</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td class='date-column'>" . htmlspecialchars($row['Date']) . "</td>";
                        echo "<td class='title-column'>
                                <a href='detail.php?No=" . htmlspecialchars($row['No']) . "&Title=" . urlencode($row['Title']) . "'>
                                    " . htmlspecialchars($row['Title']) . "
                                </a>
                              </td>";
                        echo "<td class='publisher-column'>" . htmlspecialchars($row['PublisherName']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>目前沒有公告</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </body>
</html>