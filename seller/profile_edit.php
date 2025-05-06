<?php
session_start();
include '../config.php';

// 確認使用者已登入
if (!isset($_SESSION['seller_id'])) {
    header("Location: /login.php");
    exit;
}

$id = $_SESSION['seller_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 從表單接收收款方式
    $payment_method = $_POST['payment_method'];

    // 更新收款方式
    $stmt = $conn->prepare("UPDATE sellers SET payment_method = ? WHERE id = ?");
    $stmt->execute([$payment_method, $id]);

    echo "收款方式已更新成功。";
}

// 取得目前使用者資料
$stmt = $conn->prepare("SELECT payment_method FROM sellers WHERE id = ?");
$stmt->execute([$id]);
$seller = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>編輯收款方式</title>
</head>
<body>
    <h1>編輯收款方式</h1>
    <form method="post">
        收款方式：
        <select name="payment_method">
            <option value="credit_card" <?= $seller['payment_method'] == 'credit_card' ? 'selected' : '' ?>>信用卡</option>
            <option value="bank_transfer" <?= $seller['payment_method'] == 'bank_transfer' ? 'selected' : '' ?>>銀行轉帳</option>
        </select><br>
        <button type="submit">儲存</button>
    </form>
    <a href="dashboard.php">返回儀表板</a>
</body>
</html>