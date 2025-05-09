<?php
// filepath: c:\xampp\htdocs\admin\couponHistory.php
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

// 查詢所有兌換紀錄，按日期分組
$sql = "SELECT DATE(uc.redeem_time) AS redeem_date, uc.redeem_time, c.code AS coupon_code, 
               c.discount, c.discount_type, a.Name AS user_name 
        FROM user_coupons uc
        JOIN coupons c ON uc.coupon_id = c.id
        JOIN accounts a ON uc.user_id = a.No
        ORDER BY redeem_date DESC, uc.redeem_time ASC";
$result = mysqli_query($link, $sql);

if (!$result) {
    die("查詢失敗：" . mysqli_error($link));
}

// 整理資料按日期分組
$recordsByDate = [];
while ($row = mysqli_fetch_assoc($result)) {
    $date = $row['redeem_date'];
    if (!isset($recordsByDate[$date])) {
        $recordsByDate[$date] = [];
    }
    $recordsByDate[$date][] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>優惠券兌換紀錄</title>
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
        .date-header {
            font-size: 18px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="../index.php" class="back-button">回首頁</a>
        <?php include('../userMenu.php'); ?>
    </div>

    <h1>優惠券兌換紀錄</h1>

    <?php if (!empty($recordsByDate)): ?>
        <?php foreach ($recordsByDate as $date => $records): ?>
            <div class="date-header"><?php echo htmlspecialchars($date); ?></div>
            <table>
                <thead>
                    <tr>
                        <th>兌換者</th>
                        <th>優惠券代碼</th>
                        <th>折扣</th>
                        <th>兌換時間</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($records as $record): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($record['user_name']); ?></td>
                            <td><?php echo htmlspecialchars($record['coupon_code']); ?></td>
                            <td>
                                <?php if ($record['discount_type'] === 'percentage'): ?>
                                    <?php echo htmlspecialchars($record['discount']); ?>%
                                <?php elseif ($record['discount_type'] === 'fixed'): ?>
                                    $<?php echo htmlspecialchars($record['discount']); ?>
                                <?php endif; ?>
                            </td>
                            <td><?php echo htmlspecialchars($record['redeem_time']); ?></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php endforeach; ?>
    <?php else: ?>
        <p>目前沒有兌換紀錄。</p>
    <?php endif; ?>

    <?php mysqli_close($link); ?>
</body>
</html>