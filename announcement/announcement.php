<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
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
        </style>
    </head>
    <body>
        <!-- login & register button -->
        <div class="top-right-buttons">
            <?php
                if (isset($_SESSION["user"])) {
                    echo "<span>歡迎 " . htmlspecialchars($_SESSION["name"]) . "！</span>";
                    echo "<input type='button' value='登出' onclick=\"location.href='../login/logout.php'\">";
                } else {
                    echo "<input type='button' value='登入' onclick=\"location.href='../login/login.php'\">";
                    echo "<input type='button' value='註冊' onclick=\"location.href='../login/register.php'\">";
                }
            ?>
        </div>

        <!-- 公告列表標題和新增公告按鈕 -->
        <div class="header-container">
            <h1>公告列表</h1>
            <?php
            if (isset($_SESSION["type"]) && $_SESSION["type"] === "Admin") {
                echo "<div class='admin-button'>";
                echo "<input type='button' value='新增公告' onclick=\"location.href='createNewAnnouncement.php'\">";
                echo "</div>";
            }
            ?>
        </div>

        <!-- announcement area -->
        <?php
        // 查詢公告資料
        $sql = "SELECT announcement.No, announcement.Title, announcement.Content, announcement.Date, accounts.Name AS PublisherName
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
                    <th>公告日期</th>
                    <th>公告標題</th>
                    <th>公告內容</th>
                    <th>發布者</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if (mysqli_num_rows($result) > 0) {
                    while ($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . htmlspecialchars($row['Date']) . "</td>";
                        echo "<td class='title-column'>
                                <a href='detail.php?No=" . htmlspecialchars($row['No']) . "&Title=" . urlencode($row['Title']) . "'>
                                    " . htmlspecialchars($row['Title']) . "
                                </a>
                              </td>";
                        echo "<td>" . nl2br(htmlspecialchars($row['Content'])) . "</td>";
                        echo "<td>" . htmlspecialchars($row['PublisherName']) . "</td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='4'>目前沒有公告</td></tr>";
                }
                ?>
            </tbody>
        </table>
    </body>
</html>