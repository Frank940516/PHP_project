<?php
require('../db.inc');
session_start(); // 確保啟用 session
mysqli_set_charset($link, 'utf8');

// 確認是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// 從資料庫查詢使用者類型
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

// 獲取優惠券資料
$couponId = $_GET['id'] ?? null;
if (!$couponId) {
    die("未提供優惠券 ID！");
}

$sqlCoupon = "SELECT * FROM coupons WHERE id = ?";
$stmtCoupon = mysqli_prepare($link, $sqlCoupon);
mysqli_stmt_bind_param($stmtCoupon, 'i', $couponId);
mysqli_stmt_execute($stmtCoupon);
$resultCoupon = mysqli_stmt_get_result($stmtCoupon);
$coupon = mysqli_fetch_assoc($resultCoupon);

if (!$coupon) {
    die("找不到該優惠券！");
}

// 處理表單提交
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $discount = floatval($_POST['discount']);
    $startDate = $_POST['start_date'];
    $expirationDate = $_POST['expiration_date'];

    // 根據開始日期和到期日期自動設定生效狀態
    $currentDate = date('Y-m-d');
    if ($startDate > $currentDate) {
        $isActive = 0; // 未生效
    } elseif ($expirationDate < $currentDate) {
        $isActive = 0; // 已過期
    } else {
        $isActive = 1; // 生效中
    }

    // 檢查開始日期是否是今天或未來的日期
    if ($startDate < $currentDate) {
        $message = '<span style="color: red;">開始日期不能是今天之前的日期！</span>';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $expirationDate)) {
        $message = '<span style="color: red;">無效的到期日格式！</span>';
    } elseif ($code && $discount > 0 && $startDate && $expirationDate) {
        $sqlUpdate = "UPDATE coupons SET code = ?, discount = ?, start_date = ?, expiration_date = ?, is_active = ? WHERE id = ?";
        $stmtUpdate = mysqli_prepare($link, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, 'sdssii', $code, $discount, $startDate, $expirationDate, $isActive, $couponId);

        if (mysqli_stmt_execute($stmtUpdate)) {
            if (mysqli_stmt_affected_rows($stmtUpdate) > 0) {
                // 更新成功後跳轉
                header("Location: couponList.php");
                exit();
            } else {
                $message = '<span style="color: orange;">未更新任何資料，可能是資料未變更。</span>';
            }
        } else {
            $message = '<span style="color: red;">更新失敗：' . mysqli_error($link) . '</span>';
        }
    } else {
        $message = '<span style="color: red;">請填寫所有欄位！</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯優惠券</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f7f6;
        }
        .form-container {
            max-width: 500px;
            margin: 0 auto;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .form-container h1 {
            margin-bottom: 20px;
            font-size: 24px;
            text-align: center;
        }
        .form-container label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }
        .form-container input[type="text"],
        .form-container input[type="number"],
        .form-container input[type="date"] {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #0056b3;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-coupon-list-button {
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .back-coupon-list-button:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="couponList.php" class="back-coupon-list-button">返回優惠券清單</a>
        <?php include('../userMenu.php'); ?>
    </div>

    <div class="form-container">
        <h1>編輯優惠券</h1>
        <form method="POST">
            <div class="message"><?php echo $message ?? ''; ?></div>
            <label for="code">優惠券代碼</label>
            <input type="text" id="code" name="code" value="<?php echo htmlspecialchars($coupon['code']); ?>" maxlength="20" required>

            <label for="discount">折扣 (%)</label>
            <input type="number" id="discount" name="discount" value="<?php echo htmlspecialchars($coupon['discount']); ?>" step="0.01" min="0.01" max="100" required>

            <label for="start_date">開始生效日期</label>
            <input type="date" id="start_date" name="start_date" value="<?php echo htmlspecialchars($coupon['start_date']); ?>" min="<?php echo date('Y-m-d'); ?>" required>

            <label for="expiration_date">到期日</label>
            <input type="date" id="expiration_date" name="expiration_date" value="<?php echo htmlspecialchars($coupon['expiration_date']); ?>" min="<?php echo date('Y-m-d'); ?>" required>

            <button type="submit">更新優惠券</button>
        </form>
    </div>
</body>
</html>