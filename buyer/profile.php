<?php
session_start();
include '../config.php';

// 確認使用者已登入
if (!isset($_SESSION['user_id'])) {
    header("Location: /login.php");
    exit;
}

$id = $_SESSION['user_id'];

// 取得目前使用者資料
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
?>

<!DOCTYPE html>
<html>
<head>
    <title>個人資料</title>
</head>
<body>
    <h1>個人資料</h1>
    <p><strong>姓名：</strong> <?= htmlspecialchars($user['name']) ?></p>
    <p><strong>商品寄送地址：</strong> <?= htmlspecialchars($user['address']) ?></p>
    <p><strong>生日：</strong> <?= htmlspecialchars($user['birthday']) ?></p>
    <p><strong>收款方式：</strong> 
        <?php
        switch ($user['payment_method']) {
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