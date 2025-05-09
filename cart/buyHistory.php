<?php
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
$sqlHistory = "SELECT o.id AS order_id, o.total_amount, o.created_at, o.payment_method, 
                      o.coupon_code, o.coupon_discount, -- 新增優惠券相關欄位
                      oi.product_id, p.name AS product_name, p.attachment, p.is_deleted, 
                      oi.quantity, oi.price, oi.subtotal, p.category, p.author, p.location, 
                      a.No AS seller_id, a.Name AS seller_name
               FROM orders o
               JOIN order_items oi ON o.id = oi.order_id
               JOIN products p ON oi.product_id = p.id
               JOIN accounts a ON p.seller_id = a.No
               WHERE o.user_id = ?
               ORDER BY o.created_at DESC, p.name ASC";
$stmtHistory = mysqli_prepare($link, $sqlHistory);
mysqli_stmt_bind_param($stmtHistory, 'i', $userId);
mysqli_stmt_execute($stmtHistory);
$resultHistory = mysqli_stmt_get_result($stmtHistory);

$buyRecords = [];
while ($row = mysqli_fetch_assoc($resultHistory)) {
    $buyRecords[] = $row;
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
        .product-header {
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
        .deleted-product {
            color: #e74c3c;
            font-style: italic;
        }
        .total-row {
            font-weight: bold;
            text-align: right;
            background-color: #f9f9f9;
        }
        .coupon-info {
            font-size: 14px;
            color: #28a745;
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
    <?php if (!empty($buyRecords)): ?>
        <?php 
        $currentProductId = null;
        $productTotal = 0; // 用於計算每個商品的總金額
        foreach ($buyRecords as $record): 
        ?>
            <?php if ($currentProductId !== $record['product_id']): ?>
                <?php if ($currentProductId !== null): ?>
                </table>
                <?php $productTotal = 0; // 重置總金額 ?>
                <?php endif; ?>
                <div class="product-header">
                    <div>商品名稱：<a href="../product/detail.php?id=<?php echo htmlspecialchars($record['product_id']); ?>" class="product-link">
                        <?php echo htmlspecialchars($record['product_name']); ?> 
                        (作者：<?php echo htmlspecialchars($record['author']); ?>) <!-- 顯示作者 -->
                    </a></div>
                    <div>種類：<?php echo htmlspecialchars($record['category']); ?></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>圖片</th>
                            <th>賣家</th>
                            <th>出貨地</th>
                            <th>付款方式</th> <!-- 新增付款方式欄位 -->
                            <th>購買時間</th>
                            <th>數量</th>
                            <th>單價</th>
                            <th>小計</th>
                        </tr>
                    </thead>
                    <tbody>
                <?php $currentProductId = $record['product_id']; ?>
            <?php endif; ?>
                        <tr>
                            <td>
                                <img src="../product/pic/<?php echo htmlspecialchars($record['attachment']); ?>" alt="<?php echo htmlspecialchars($record['product_name']); ?>" class="product-image">
                            </td>
                            <td>
                                <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($record['seller_id']); ?>" class="seller-link">
                                    <?php echo htmlspecialchars($record['seller_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($record['location']); ?></td>
                            <td><?php echo htmlspecialchars($record['payment_method']); ?></td> <!-- 顯示付款方式 -->
                            <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($record['price']); ?></td>
                            <td><?php echo htmlspecialchars($record['subtotal']); ?></td>
                        </tr>
                        <?php $productTotal += $record['subtotal']; // 累加小計到總金額 ?>
            <?php if (end($buyRecords) === $record || $currentProductId !== $buyRecords[array_search($record, $buyRecords) + 1]['product_id']): ?>
                    <tr class="total-row">
                        <td colspan="6">
                            <?php if (!empty($record['coupon_code'])): ?>
                                <div class="coupon-info">
                                    使用優惠券：<?php echo htmlspecialchars($record['coupon_code']); ?>，折扣金額：<?php echo htmlspecialchars($record['coupon_discount']); ?>
                                </div>
                            <?php endif; ?>
                        </td>
                        <td>總金額：</td>
                        <td><?php echo htmlspecialchars($record['total_amount']); ?></td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>目前沒有購買紀錄。</p>
    <?php endif; ?>
</body>
</html>