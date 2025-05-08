<?php
require('../db.inc');
session_start(); // 確保啟用 session
mysqli_set_charset($link, 'utf8');

// 更新優惠券狀態
include('updateCoupon.php'); // 每次載入清單時執行更新邏輯

// 確認是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// 從資料庫查詢使用者類型
$userEmail = $_SESSION['user'];
$sqlUserType = "SELECT No, Type FROM accounts WHERE Email = ?";
$stmtUserType = mysqli_prepare($link, $sqlUserType);
mysqli_stmt_bind_param($stmtUserType, 's', $userEmail);
mysqli_stmt_execute($stmtUserType);
$resultUserType = mysqli_stmt_get_result($stmtUserType);
$user = mysqli_fetch_assoc($resultUserType);

if (!$user) {
    die("使用者不存在！");
}

$userId = $user['No'];
$isAdmin = $user['Type'] === 'Admin'; // 判斷是否為管理員

// 處理兌換結果
$redeemMessage = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['redeem_code'])) {
    $redeemCode = trim($_POST['redeem_code']);
    $sqlRedeem = "SELECT id, is_active FROM coupons WHERE code = ? AND expiration_date >= CURDATE()";
    $stmtRedeem = mysqli_prepare($link, $sqlRedeem);
    mysqli_stmt_bind_param($stmtRedeem, 's', $redeemCode);
    mysqli_stmt_execute($stmtRedeem);
    $resultRedeem = mysqli_stmt_get_result($stmtRedeem);
    $coupon = mysqli_fetch_assoc($resultRedeem);

    if ($coupon && $coupon['is_active']) {
        // 檢查是否已兌換
        $sqlCheck = "SELECT * FROM user_coupons WHERE user_id = ? AND coupon_id = ?";
        $stmtCheck = mysqli_prepare($link, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, 'ii', $userId, $coupon['id']);
        mysqli_stmt_execute($stmtCheck);
        $resultCheck = mysqli_stmt_get_result($stmtCheck);

        if (mysqli_num_rows($resultCheck) === 0) {
            // 新增到 user_coupons
            $sqlInsert = "INSERT INTO user_coupons (user_id, coupon_id) VALUES (?, ?)";
            $stmtInsert = mysqli_prepare($link, $sqlInsert);
            mysqli_stmt_bind_param($stmtInsert, 'ii', $userId, $coupon['id']);
            mysqli_stmt_execute($stmtInsert);
            $redeemMessage = '<span style="color: green;">優惠券兌換成功！</span>';
        } else {
            $redeemMessage = '<span style="color: orange;">您已兌換過此優惠券！</span>';
        }
    } else {
        $redeemMessage = '<span style="color: red;">無效的優惠券兌換碼！</span>';
    }
}

// 查詢優惠券
if ($isAdmin) {
    // 管理員查看所有優惠券
    $sql = "SELECT id, code, discount, start_date, expiration_date, is_active FROM coupons ORDER BY start_date ASC, expiration_date ASC";
    $stmt = mysqli_prepare($link, $sql);
} else {
    // 一般使用者僅查看已兌換的有效優惠券
    $sql = "SELECT c.id, c.code, c.discount, c.start_date, c.expiration_date 
            FROM coupons c
            JOIN user_coupons uc ON c.id = uc.coupon_id
            WHERE uc.user_id = ? AND c.is_active = 1 AND c.expiration_date >= CURDATE() AND c.start_date <= CURDATE()
            ORDER BY c.start_date ASC, c.expiration_date ASC";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 'i', $userId);
}
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
    <title>優惠券清單</title>
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
        .back-home-button {
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .back-home-button:hover {
            background-color: #0056b3;
        }
        .add-coupon-button {
            text-decoration: none;
            color: white;
            background-color: #28a745;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .add-coupon-button:hover {
            background-color: #218838;
        }
        .redeem-section {
            margin-bottom: 20px;
        }
        .redeem-section input[type="text"] {
            padding: 8px;
            font-size: 14px;
            width: 200px;
        }
        .redeem-section button {
            padding: 8px 15px;
            font-size: 14px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .redeem-section button:hover {
            background-color: #218838;
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
        .action-button {
            padding: 5px 10px;
            font-size: 14px;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .edit-button {
            background-color: #ffc107;
            color: white;
        }
        .edit-button:hover {
            background-color: #e0a800;
        }
        .delete-button {
            background-color: #dc3545;
            color: white;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .no-coupons {
            font-size: 16px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="../index.php" class="back-home-button">返回首頁</a>
        <?php if ($isAdmin): ?>
            <a href="addCoupon.php" class="add-coupon-button">新增優惠券</a>
        <?php endif; ?>
        <?php include('../userMenu.php'); ?>
    </div>

    <h1>優惠券清單</h1>

    <?php if (!$isAdmin): ?>
        <div class="redeem-section">
            <form method="POST">
                <input type="text" name="redeem_code" placeholder="輸入優惠券代碼" required>
                <button type="submit">兌換</button>
            </form>
            <div><?php echo $redeemMessage; ?></div>
        </div>
    <?php endif; ?>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>優惠券代碼</th>
                    <th>折扣 (%)</th>
                    <th>開始生效日期</th>
                    <th>到期日</th>
                    <?php if ($isAdmin): ?>
                        <th>狀態</th>
                        <th>操作</th>
                    <?php endif; ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['code']); ?></td>
                        <td><?php echo htmlspecialchars($row['discount']); ?>%</td>
                        <td><?php echo htmlspecialchars($row['start_date']); ?></td>
                        <td><?php echo htmlspecialchars($row['expiration_date']); ?></td>
                        <?php if ($isAdmin): ?>
                            <td>
                                <?php if ($row['is_active'] == 1): ?>
                                    <span style="color: green;">有效</span>
                                <?php elseif ($row['is_active'] == 0): ?>
                                    <span style="color: red;">無效</span>
                                <?php elseif ($row['is_active'] == 2): ?>
                                    <span style="color: orange;">未生效</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <form action="editCoupon.php" method="GET" style="display: inline;">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button type="submit" class="action-button edit-button">編輯</button>
                                </form>
                                <form action="deleteCoupon.php" method="POST" style="display: inline;" onsubmit="return confirm('確定要刪除此優惠券嗎？')">
                                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                    <button type="submit" class="action-button delete-button">刪除</button>
                                </form>
                            </td>
                        <?php endif; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="no-coupons">目前沒有可用的優惠券。</p>
    <?php endif; ?>

    <?php mysqli_close($link); ?>
</body>
</html>