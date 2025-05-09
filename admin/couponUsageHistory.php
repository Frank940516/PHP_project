<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認是否為管理員
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$userEmail = $_SESSION['user'];

// 查詢使用者是否為管理員
$sqlAdminCheck = "SELECT Type FROM accounts WHERE Email = ?";
$stmtAdminCheck = mysqli_prepare($link, $sqlAdminCheck);
mysqli_stmt_bind_param($stmtAdminCheck, 's', $userEmail);
mysqli_stmt_execute($stmtAdminCheck);
$resultAdminCheck = mysqli_stmt_get_result($stmtAdminCheck);
$user = mysqli_fetch_assoc($resultAdminCheck);

if (!$user || $user['Type'] !== 'Admin') {
    echo "您沒有權限訪問此頁面！";
    exit();
}

// 查詢所有使用者的優惠券使用紀錄
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
    echo "查詢失敗：" . mysqli_error($link);
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>優惠券使用紀錄</title>
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
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
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
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="../admin/adminDashboard.php" class="back-button">返回管理員主頁</a>
        <?php include('../userMenu.php'); ?>
    </div>

    <h1>優惠券使用紀錄</h1>

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