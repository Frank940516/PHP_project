<?php
// filepath: c:\xampp\htdocs\admin\couponHistory.php
require('../db.inc');
session_start();
mysqli_set_charset($link, 'utf8');

// 確認是否為管理員
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}
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

// 兌換紀錄
$sqlRedeem = "SELECT DATE(uc.redeem_time) AS redeem_date, uc.redeem_time, c.code AS coupon_code, 
               c.discount, c.discount_type, a.Name AS user_name 
        FROM user_coupons uc
        JOIN coupons c ON uc.coupon_id = c.id
        JOIN accounts a ON uc.user_id = a.No
        ORDER BY redeem_date DESC, uc.redeem_time ASC";
$resultRedeem = mysqli_query($link, $sqlRedeem);
if (!$resultRedeem) {
    die("查詢兌換紀錄失敗：" . mysqli_error($link));
}
$recordsByDate = [];
while ($row = mysqli_fetch_assoc($resultRedeem)) {
    $date = $row['redeem_date'];
    if (!isset($recordsByDate[$date])) {
        $recordsByDate[$date] = [];
    }
    $recordsByDate[$date][] = $row;
}

// 使用紀錄
$sqlCouponUsage = "SELECT o.id AS order_id, o.created_at AS usage_time, o.coupon_code, o.coupon_discount, 
                          a.Name AS user_name, a.Email AS user_email, 
                          c.discount_type, c.discount, c.start_date, c.expiration_date
                   FROM orders o
                   JOIN accounts a ON o.user_id = a.No
                   LEFT JOIN coupons c ON o.coupon_code = c.code
                   WHERE o.coupon_code IS NOT NULL
                   ORDER BY o.created_at DESC";
$resultCouponUsage = mysqli_query($link, $sqlCouponUsage);
if (!$resultCouponUsage) {
    die("查詢使用紀錄失敗：" . mysqli_error($link));
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>優惠券兌換與使用紀錄</title>
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
            margin-bottom: 30px;
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
        h1 {
            margin-bottom: 10px;
        }
        h2 {
            margin-top: 40px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="../index.php" class="back-button">回首頁</a>
        <?php include('../userMenu.php'); ?>
    </div>

    <h1>優惠券兌換與使用紀錄</h1>

    <h2>優惠券兌換紀錄</h2>
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

    <h2>優惠券使用紀錄</h2>
    <?php if (mysqli_num_rows($resultCouponUsage) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>訂單編號</th>
                    <th>使用者名稱</th>
                    <th>使用者 Email</th>
                    <th>優惠券代碼</th>
                    <th>折扣值</th>
                    <th>開始日期</th>
                    <th>到期日期</th>
                    <th>使用時間</th>
                    <th>折扣金額</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($resultCouponUsage)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['order_id']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['user_email']); ?></td>
                        <td><?php echo htmlspecialchars($row['coupon_code']); ?></td>
                        <td>
                            <?php echo $row['discount_type'] === 'percentage'
                                ? htmlspecialchars($row['discount']) . '%'
                                : '$' . htmlspecialchars($row['discount']); ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['expiration_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['usage_time']); ?></td>
                        <td><?php echo htmlspecialchars($row['coupon_discount']); ?></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>目前沒有優惠券使用紀錄。</p>
    <?php endif; ?>

    <?php mysqli_close($link); ?>
</body>
</html>