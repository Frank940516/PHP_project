<?php
session_start();
include '../config.php';

// 確認使用者已登入
if (!isset($_SESSION['seller_id'])) {
    header("Location: /login.php");
    exit;
}

$id = $_SESSION['seller_id'];

// 取得目前使用者資料
$stmt = $conn->prepare("SELECT * FROM sellers WHERE id = ?");
$stmt->execute([$id]);
$seller = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>賣家個人資料</title>
</head>
<body>
    <h1>賣家個人資料</h1>
    <p><strong>姓名：</strong> <?= htmlspecialchars($seller['name']) ?></p>
    <p><strong>電子郵件：</strong> <?= htmlspecialchars($seller['email']) ?></p>
    <p><strong>聯絡電話：</strong> <?= htmlspecialchars($seller['phone']) ?></p>
    <p><strong>地址：</strong> <?= htmlspecialchars($seller['address']) ?></p>
    <p><strong>收款方式：</strong>
        <?php
        switch ($seller['payment_method']) {
            case 'credit_card':
                echo '信用卡';
                break;
            case 'bank_transfer':
                echo '銀行轉帳';
                break;
            default:
                echo '未設定';
        }
        ?>
    </p>
    <a href="profile_edit.php">編輯收款方式</a>
    <a href="dashboard.php">返回儀表板</a>
</body>
</html>