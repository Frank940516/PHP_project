<?php
require_once('../db.inc');
session_start();

// 確保使用者已登入
if (!isset($_SESSION["user"])) {
    header("Location: ../login/login.php");
    exit();
}

// 查詢該使用者的所有通知
$userEmail = $_SESSION["user"];
$sqlUser = "SELECT No FROM accounts WHERE Email = ?";
$stmtUser = mysqli_prepare($link, $sqlUser);
mysqli_stmt_bind_param($stmtUser, 's', $userEmail);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);

if (!$user) {
    echo "使用者不存在！";
    exit();
}

$userId = $user['No'];
$sql = "SELECT message, created_at, product_id FROM notifications WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$notifications = [];
while ($row = mysqli_fetch_assoc($result)) {
    $notifications[] = $row;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>通知列表</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        .announcement-button {
            background-color: #007BFF;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
        }
        .announcement-button:hover {
            background-color: #0056b3;
        }
        .top-right-buttons {
            display: flex;
            gap: 10px;
        }
        .notification-list {
            max-width: 800px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .notification-item {
            padding: 10px 15px;
            border-bottom: 1px solid #ddd;
        }
        .notification-item:last-child {
            border-bottom: none;
        }
        .notification-item small {
            display: block;
            color: #888;
            margin-top: 5px;
        }
        .clickable {
            color: blue;
            text-decoration: underline;
            cursor: pointer;
        }
    </style>
    <script>
        function navigateTo(url) {
            window.location.href = url;
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <a href="../index.php" class="announcement-button">首頁</a>
        <div class="top-right-buttons">
            <?php include('../userMenu.php'); ?>
        </div>
    </div>

    <div class="notification-list">
        <h2>通知列表</h2>
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $notification): ?>
                <div class="notification-item">
                    <?php
                    $msg = $notification['message'];
                    $productId = $notification['product_id']; // 從資料庫中獲取 product_id
                    if ($productId) {
                        $productLink = "/product/detail.php?id={$productId}";
                        // 替換書名為超連結
                        $msg = preg_replace(
                            '/書籍「(.+?)」/',
                            '書籍「<a href="' . $productLink . '">' . htmlspecialchars('$1') . '</a>」',
                            $msg
                        );
                    }
                    echo $msg;
                    ?>
                    <small><?php echo htmlspecialchars($notification['created_at']); ?></small>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <p>目前沒有通知。</p>
        <?php endif; ?>
    </div>
</body>
</html>