<?php
// filepath: c:\xampp\htdocs\announcement\search.php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

$query = isset($_GET['query']) ? trim($_GET['query']) : '';

$sql = "SELECT announcement.No, announcement.Title, announcement.Date, accounts.Name AS PublisherName
        FROM announcement
        JOIN accounts ON announcement.Publisher = accounts.No
        WHERE announcement.Title LIKE ? OR announcement.Content LIKE ?
        ORDER BY Date DESC";
$stmt = mysqli_prepare($link, $sql);
$searchTerm = '%' . $query . '%';
mysqli_stmt_bind_param($stmt, 'ss', $searchTerm, $searchTerm);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搜尋公告</title>
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

        /* 返回按鈕樣式 */
        .back-button {
            display: inline-block;
            padding: 10px 15px;
            font-size: 14px;
            color: #fff;
            background-color: #4CAF50;
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
            margin-bottom: 20px;
        }

        .back-button:hover {
            background-color: #45a049;
        }

        /* 頂部工具列樣式 */
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="announcement.php" class="back-button">返回公告頁面</a>
        <?php include('../userMenu.php'); ?> <!-- 引入使用者功能清單 -->
    </div>
    <h1>搜尋結果</h1>
    <table>
        <thead>
            <tr>
                <th>公告時間</th>
                <th>公告標題</th>
                <th>發布者</th>
            </tr>
        </thead>
        <tbody>
            <?php if (mysqli_num_rows($result) > 0): ?>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['Date']); ?></td>
                        <td>
                            <a href="detail.php?No=<?php echo htmlspecialchars($row['No']); ?>">
                                <?php echo htmlspecialchars($row['Title']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($row['PublisherName']); ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3">沒有符合的公告</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>