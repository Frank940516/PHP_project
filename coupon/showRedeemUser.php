<?php
require('../db.inc');
session_start();
mysqli_set_charset($link, 'utf8');

// 確認是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// 確認使用者是否為管理員
$userEmail = $_SESSION['user'];
$sqlUserType = "SELECT Type FROM accounts WHERE Email = ?";
$stmtUserType = mysqli_prepare($link, $sqlUserType);
mysqli_stmt_bind_param($stmtUserType, 's', $userEmail);
mysqli_stmt_execute($stmtUserType);
$resultUserType = mysqli_stmt_get_result($stmtUserType);
$user = mysqli_fetch_assoc($resultUserType);

if (!$user || $user['Type'] !== 'Admin') {
    die("您沒有權限訪問此頁面！");
}

// 確認是否有 coupon_id
if (!isset($_GET['coupon_id'])) {
    die("未指定優惠券 ID！");
}

$couponId = intval($_GET['coupon_id']);

// 查詢優惠券兌換紀錄
$sql = "SELECT uc.redeem_time, a.Name AS user_name 
        FROM user_coupons uc
        JOIN accounts a ON uc.user_id = a.No
        WHERE uc.coupon_id = ?
        ORDER BY uc.redeem_time ASC";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'i', $couponId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if (!$result) {
    die("查詢失敗：" . mysqli_error($link));
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>兌換紀錄</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f7f6;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-button {
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .back-button:hover {
            background-color: #0056b3;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="couponList.php" class="back-button">返回優惠券清單</a>
        <?php include('../userMenu.php'); ?>
    </div>

    <h1>兌換紀錄</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>兌換者</th>
                    <th>兌換時間</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['redeem_time']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>目前沒有兌換紀錄。</p>
    <?php endif; ?>

    <?php mysqli_close($link); ?>
</body>
</html>