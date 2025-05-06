<?php
session_start();
include '../config.php';

// 確認使用者已登入
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 從表單接收資料
    $name = $_POST['name'];
    $address = $_POST['address']; // 更新為商品寄送地址
    $birthday = $_POST['birthday'];
    $payment_method = $_POST['payment_method'];

    // 更新使用者資料
    $stmt = $conn->prepare("UPDATE users SET name = ?, address = ?, birthday = ?, payment_method = ? WHERE id = ?");
    $stmt->execute([$name, $address, $birthday, $payment_method, $id]);

    echo "個人資料已更新成功。";
}

// 取得目前使用者資料
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>編輯個人資料</title>
</head>
<body>
    <h1>編輯個人資料</h1>
    <form method="post">
        姓名：<input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"><br>
        商品寄送地址：<input type="text" name="address" value="<?= htmlspecialchars($user['address']) ?>"><br>
        生日：<input type="date" name="birthday" value="<?= htmlspecialchars($user['birthday']) ?>"><br>
        付款方式：
        <select name="payment_method">
            <option value="credit_card" <?= $user['payment_method'] == 'credit_card' ? 'selected' : '' ?>>信用卡</option>
            <option value="paypal" <?= $user['payment_method'] == 'paypal' ? 'selected' : '' ?>>PayPal</option>
            <option value="bank_transfer" <?= $user['payment_method'] == 'bank_transfer' ? 'selected' : '' ?>>銀行轉帳</option>
        </select><br>
        <button type="submit">儲存</button>
    </form>
    <a href="dashboard.php">返回儀表板</a>
</body>
</html>