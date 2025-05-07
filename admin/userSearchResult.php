<?php
session_start();
require('../db.inc');

// 檢查是否已登入
if (!isset($_SESSION['user']) || $_SESSION['type'] !== 'Admin') {
    echo "您無權訪問此頁面！";
    exit();
}

// 獲取搜尋查詢
$query = isset($_GET['query']) ? trim($_GET['query']) : '';
if (empty($query)) {
    echo "請輸入搜尋條件！";
    exit();
}

// 查詢使用者資料
$sql = "SELECT * FROM accounts WHERE Name LIKE ? OR Email LIKE ?";
$stmt = mysqli_prepare($link, $sql);
$searchTerm = '%' . $query . '%';
mysqli_stmt_bind_param($stmt, 'ss', $searchTerm, $searchTerm);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// 獲取當前登入的使用者 ID
$currentUserId = $_SESSION['user'];

?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>搜尋結果</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
            vertical-align: middle;
        }
        th {
            background-color: #f8f9fa;
        }
        .home-button {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            margin-bottom: 20px;
            display: inline-block;
        }
        .home-button:hover {
            background-color: #0056b3;
        }
        .action-button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
        }
        .promote {
            background-color: #007BFF;
            color: white;
        }
        .demote {
            background-color: #FFC107;
            color: white;
        }
        .block {
            background-color: #DC3545;
            color: white;
        }
        .unblock {
            background-color: #28a745;
            color: white;
        }
        .action-button:hover {
            opacity: 0.8;
        }
        .block-reason-form textarea {
            width: calc(100% - 10px);
            margin-bottom: 5px;
            box-sizing: border-box;
            min-height: 50px;
        }
        .block-reason-form button {
            width: auto;
            padding: 5px 10px;
        }
        .block-reason-form {
            margin-top: 5px;
            padding: 5px;
            border: 1px solid #eee;
            background-color: #f9f9f9;
            border-radius: 4px;
        }
    </style>
    <script>
        function toggleBlockReasonForm(userId) {
            const form = document.getElementById(`blockForm-${userId}`);
            if (form) {
                if (form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'block';
                } else {
                    form.style.display = 'none';
                }
            }
        }
    </script>
</head>
<body>
    <a href="userManagement.php" class="home-button">返回使用者管理</a>
    <h1>搜尋結果</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>名稱</th>
                    <th>電子郵件</th>
                    <th>類型</th>
                    <th>狀態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['No']); ?></td>
                        <td><?php echo htmlspecialchars($row['Name']); ?></td>
                        <td><?php echo htmlspecialchars($row['Email']); ?></td>
                        <td><?php echo htmlspecialchars($row['Type']); ?></td>
                        <td><?php echo htmlspecialchars($row['Status']); ?></td>
                        <td>
                            <?php if ((int)$row['No'] !== (int)$currentUserId): ?>
                                <?php if ($row['Status'] !== 'blocked'): ?>
                                    <?php if ($row['Type'] === 'User'): ?>
                                        <form method="POST" action="updateUser.php" style="display: inline;" onsubmit="return confirm('確定要升級為管理員嗎？')">
                                            <input type="hidden" name="user_id" value="<?php echo $row['No']; ?>">
                                            <input type="hidden" name="action" value="promote">
                                            <button type="submit" class="action-button promote">升為管理員</button>
                                        </form>
                                    <?php elseif ($row['Type'] === 'Admin'): ?>
                                        <form method="POST" action="updateUser.php" style="display: inline;" onsubmit="return confirm('確定要降級為一般使用者嗎？')">
                                            <input type="hidden" name="user_id" value="<?php echo $row['No']; ?>">
                                            <input type="hidden" name="action" value="demote">
                                            <button type="submit" class="action-button demote">降為一般使用者</button>
                                        </form>
                                    <?php endif; ?>

                                    <button type="button" class="action-button block" onclick="toggleBlockReasonForm('<?php echo $row['No']; ?>')">封鎖使用者</button>
                                    <div id="blockForm-<?php echo $row['No']; ?>" style="display: none;" class="block-reason-form">
                                        <form method="POST" action="updateUser.php" onsubmit="return confirm('確定要封鎖該使用者嗎？')">
                                            <input type="hidden" name="user_id" value="<?php echo $row['No']; ?>">
                                            <input type="hidden" name="action" value="block">
                                            <textarea name="block_reason" placeholder="輸入封鎖原因 (最多100字)" required maxlength="100"></textarea>
                                            <button type="submit" class="action-button block">確認封鎖</button>
                                        </form>
                                    </div>

                                <?php elseif ($row['Status'] === 'blocked'): ?>
                                    <form method="POST" action="updateUser.php" style="display: inline;" onsubmit="return confirm('確定要解封該使用者嗎？')">
                                        <input type="hidden" name="user_id" value="<?php echo $row['No']; ?>">
                                        <input type="hidden" name="action" value="unblock">
                                        <button type="submit" class="action-button unblock">解封使用者</button>
                                    </form>
                                <?php endif; ?>
                            <?php else: ?>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>找不到符合條件的使用者。</p>
    <?php endif; ?>

    <?php mysqli_close($link); ?>
</body>
</html>