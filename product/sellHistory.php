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

// 查詢販售商品的購買紀錄，按購買時間降序排列
$sqlSellHistory = "SELECT oi.product_id, p.name AS product_name, p.attachment, p.category, 
                          o.created_at, o.total_amount, oi.quantity, oi.price, oi.subtotal, 
                          a.No AS buyer_id, a.Name AS buyer_name
                   FROM orders o
                   JOIN order_items oi ON o.id = oi.order_id
                   JOIN products p ON oi.product_id = p.id
                   JOIN accounts a ON o.user_id = a.No
                   WHERE p.seller_id = ?
                   ORDER BY o.created_at DESC"; // 按購買時間降序排列
$stmtSellHistory = mysqli_prepare($link, $sqlSellHistory);
mysqli_stmt_bind_param($stmtSellHistory, 'i', $userId);
mysqli_stmt_execute($stmtSellHistory);
$resultSellHistory = mysqli_stmt_get_result($stmtSellHistory);

$sellRecords = [];
while ($row = mysqli_fetch_assoc($resultSellHistory)) {
    $sellRecords[] = $row;
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>販售紀錄</title>
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
        .product-link, .buyer-link {
            text-decoration: none;
            color: #007BFF;
        }
        .product-link:hover, .buyer-link:hover {
            text-decoration: underline;
        }
        .total-row {
            font-weight: bold;
            text-align: right;
            background-color: #f9f9f9;
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

    <h1>販售紀錄</h1>
    <?php if (!empty($sellRecords)): ?>
        <?php 
        $currentProductId = null;
        $productTotal = 0; // 用於計算每個商品的總金額
        foreach ($sellRecords as $record): 
        ?>
            <?php if ($currentProductId !== $record['product_id']): ?>
                <?php if ($currentProductId !== null): ?>
                    </tbody>
                </table>
                <?php $productTotal = 0; // 重置總金額 ?>
                <?php endif; ?>
                <div class="product-header">
                    <div>商品名稱：<a href="detail.php?id=<?php echo htmlspecialchars($record['product_id']); ?>" class="product-link"><?php echo htmlspecialchars($record['product_name']); ?></a></div>
                    <div>種類：<?php echo htmlspecialchars($record['category']); ?></div>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>圖片</th>
                            <th>購買者</th>
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
                                <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($record['buyer_id']); ?>" class="buyer-link">
                                    <?php echo htmlspecialchars($record['buyer_name']); ?>
                                </a>
                            </td>
                            <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                            <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                            <td><?php echo htmlspecialchars($record['price']); ?></td>
                            <td><?php echo htmlspecialchars($record['subtotal']); ?></td>
                        </tr>
                        <?php $productTotal += $record['subtotal']; // 累加小計到總金額 ?>
            <?php if (end($sellRecords) === $record || $currentProductId !== $sellRecords[array_search($record, $sellRecords) + 1]['product_id']): ?>
                    <!-- 顯示最後一個商品的總金額 -->
                    <tr class="total-row">
                        <td colspan="5">總金額：</td>
                        <td><?php echo htmlspecialchars($productTotal); ?></td>
                    </tr>
                    </tbody>
                </table>
            <?php endif; ?>
        <?php endforeach; ?>
    <?php else: ?>
        <p>目前沒有販售紀錄。</p>
    <?php endif; ?>
</body>
</html>