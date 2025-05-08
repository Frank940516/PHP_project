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

// 處理表單提交
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $code = trim($_POST['code']);
    $discount = floatval($_POST['discount']);
    $discount = ($discount == intval($discount)) ? intval($discount) : $discount;
    $discountType = $_POST['discount_type'];
    $startDate = $_POST['start_date'];
    $expirationDate = $_POST['expiration_date'];
    $redeemLimit = intval($_POST['redeem_limit']);

    // 根據開始日期自動設定生效狀態
    $currentDate = date('Y-m-d');
    $isActive = ($startDate <= $currentDate) ? 1 : 0;

    // 檢查開始日期是否是今天或未來的日期
    if ($startDate < $currentDate) {
        $message = '<span style="color: red;">開始日期不能是今天之前的日期！</span>';
    } elseif ($code && $discount > 0 && $startDate && $expirationDate) {
        // 檢查是否有重複的優惠券代碼
        $sqlCheck = "SELECT id FROM coupons WHERE code = ?";
        $stmtCheck = mysqli_prepare($link, $sqlCheck);
        mysqli_stmt_bind_param($stmtCheck, 's', $code);
        mysqli_stmt_execute($stmtCheck);
        mysqli_stmt_store_result($stmtCheck);
        if (mysqli_stmt_num_rows($stmtCheck) > 0) {
            $message = '<span style="color: red;">已有相同名稱的優惠券，請更換優惠券代碼！</span>';
        } else {
            $sqlInsert = "INSERT INTO coupons (code, discount, discount_type, start_date, expiration_date, is_active, redeem_limit) VALUES (?, ?, ?, ?, ?, ?, ?)";
            $stmtInsert = mysqli_prepare($link, $sqlInsert);
            mysqli_stmt_bind_param($stmtInsert, 'sdsssii', $code, $discount, $discountType, $startDate, $expirationDate, $isActive, $redeemLimit);
            if (mysqli_stmt_execute($stmtInsert)) {
                $message = '<span style="color: green;">優惠券新增成功！</span>';
            } else {
                $message = '<span style="color: red;">新增失敗，請檢查輸入資料！</span>';
            }
        }
        mysqli_stmt_close($stmtCheck);
    } else {
        $message = '<span style="color: red;">請填寫所有欄位！</span>';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>新增優惠券</title>
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
        .form-container input[type="date"],
        .form-container select {
            width: 100%;
            padding: 10px;
            margin-bottom: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
        }
        .form-container input[type="checkbox"] {
            margin-right: 5px;
        }
        .form-container button {
            width: 100%;
            padding: 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
        }
        .form-container button:hover {
            background-color: #218838;
        }
        .message {
            text-align: center;
            margin-bottom: 15px;
        }
        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            text-decoration: none;
            color: #007BFF;
        }
        .back-link:hover {
            text-decoration: underline;
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
        <h1>新增優惠券</h1>
        <form method="POST">
            <div class="message"><?php echo $message; ?></div>
            <label for="code">優惠券代碼</label>
            <input type="text" id="code" name="code" placeholder="輸入優惠券代碼" maxlength="20" required>

            <label for="discount_type">折扣類型</label>
            <select id="discount_type" name="discount_type" required>
                <option value="percentage">百分比折扣</option>
                <option value="fixed">固定金額折扣</option>
            </select>

            <label for="discount">折扣值</label>
            <input type="number" id="discount" name="discount" placeholder="輸入折扣值" step="0.01" min="0.01" required>

            <label for="start_date">開始生效日期</label>
            <input type="date" id="start_date" name="start_date" min="<?php echo date('Y-m-d'); ?>" required>

            <label for="expiration_date">到期日</label>
            <input type="date" id="expiration_date" name="expiration_date" min="<?php echo date('Y-m-d'); ?>" required>

            <label for="redeem_limit">兌換次數限制</label>
            <input type="number" id="redeem_limit" name="redeem_limit" placeholder="輸入兌換次數限制" min="1" value="1" required>

            <button type="submit">新增優惠券</button>
        </form>
        <a href="couponList.php" class="back-link">返回優惠券清單</a>
    </div>
</body>
</html>