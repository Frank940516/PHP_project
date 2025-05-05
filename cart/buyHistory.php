<?php
// filepath: c:\xampp\htdocs\cart\buyHistory.php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$userEmail = $_SESSION['user'];

// 查詢使用者 ID
$sqlUser = "SELECT No FROM accounts WHERE Email = ?";
$stmtUser = mysqli_prepare($link, $sqlUser);
mysqli_stmt_bind_param($stmtUser, 's', $userEmail);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);

if (!$user) {
    echo "使用者不存在！";
    exit();
}

$userId = $user['No'];

// 查詢購買紀錄
$sqlHistory = "SELECT o.id AS order_id, o.total_amount, o.created_at, 
                      oi.product_id, p.name AS product_name, p.attachment, oi.quantity, oi.price, oi.subtotal
               FROM orders o
               JOIN order_items oi ON o.id = oi.order_id
               JOIN products p ON oi.product_id = p.id
               WHERE o.user_id = ?
               ORDER BY o.created_at DESC";
$stmtHistory = mysqli_prepare($link, $sqlHistory);
mysqli_stmt_bind_param($stmtHistory, 'i', $userId);
mysqli_stmt_execute($stmtHistory);
$resultHistory = mysqli_stmt_get_result($stmtHistory);

$orders = [];
while ($row = mysqli_fetch_assoc($resultHistory)) {
    $orders[] = $row;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>購買紀錄</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 20px;
            background-color: #f8f9fa;
            border-bottom: 1px solid #ddd;
        }
        .back-home-button {
            font-size: 16px;
        }
        .back-home-button input {
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
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
        .order-header {
            background-color: #e9ecef;
            font-weight: bold;
            padding: 10px;
        }
        .product-image {
            width: 100px;
            height: auto;
        }
        .product-link {
            text-decoration: none;
            color: #007BFF;
        }
        .product-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <div class="back-home-button">
            <input type="button" value="返回首頁" onclick="location.href='../index.php'">
        </div>
        <?php require('../userMenu.php'); ?>
    </div>

    <h1>購買紀錄</h1>
    <?php if (!empty($orders)): ?>
        <?php 
        $currentOrderId = null;
        foreach ($orders as $order): 
        ?>
            <?php if ($currentOrderId !== $order['order_id']): ?>
                <div class="order-header">
                    <span>購買時間：<?php echo htmlspecialchars($order['created_at']); ?></span>
                    <span style="float: right;">訂單總金額：<?php echo htmlspecialchars($order['total_amount']); ?></span>
                </div>
                <?php $currentOrderId = $order['order_id']; ?>
                <table>
                    <thead>
                        <tr>
                            <th>圖片</th>
                            <th>商品名稱</th>
                            <th>數量</th>
                            <th>單價</th>
                            <th>小計</th>
                        </tr>
                    </thead>
                    <tbody>
            <?php endif; ?>
                        <tr>
                            <td>
                                <img src="../product/pic/<?php echo htmlspecialchars($order['attachment']); ?>" alt="<?php echo htmlspecialchars($order['product_name']); ?>" class="product-image">
                            </td>
                            <td>
                                <a href="../product/detail.php?id=<?php echo htmlspecialchars($order['product_id']); ?>" class="product-link">
                                    <?php echo htmlspecialchars($order['product_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($order['price']); ?></td>
                            <td><?php echo htmlspecialchars($order['subtotal']); ?></td>
                        </tr>
            <?php if (end($orders) === $order || $currentOrderId !== $orders[array_search($order, $orders) + 1]['order_id']): ?>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>目前沒有購買紀錄。</p>
    <?php endif; ?>
</body>
</html>