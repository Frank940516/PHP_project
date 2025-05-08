<?php
require('../db.inc');
session_start(); // 確保啟用 session
mysqli_set_charset($link, 'utf8');

// 查詢所有交易紀錄，按日期分類
$sql = "SELECT o.id AS order_id, o.total_amount, o.created_at, o.payment_method, 
               oi.product_id, p.name AS product_name, p.author, p.location, 
               oi.quantity, oi.price, oi.subtotal, 
               u.No AS buyer_id, u.Name AS buyer_name, 
               a.No AS seller_id, a.Name AS seller_name
        FROM orders o
        JOIN order_items oi ON o.id = oi.order_id
        JOIN products p ON oi.product_id = p.id
        JOIN accounts u ON o.user_id = u.No
        JOIN accounts a ON p.seller_id = a.No
        ORDER BY o.created_at DESC";
$result = mysqli_query($link, $sql);

if (!$result) {
    die("查詢失敗：" . mysqli_error($link));
}

$transactions = [];
while ($row = mysqli_fetch_assoc($result)) {
    $date = date('Y-m-d', strtotime($row['created_at']));
    $transactions[$date][] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>交易紀錄</title>
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
        .date-section {
            margin-bottom: 30px;
        }
        .date-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 10px;
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
        .product-name a, .buyer-name a, .seller-name a {
            text-decoration: none;
            color: #007BFF;
        }
        .product-name a:hover, .buyer-name a:hover, .seller-name a:hover {
            text-decoration: underline;
        }
        .no-records {
            font-size: 16px;
            color: #555;
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <a href="../index.php" class="back-home-button">返回首頁</a>
        <?php include('../userMenu.php'); ?>
    </div>

    <h1>交易紀錄</h1>

    <?php if (!empty($transactions)): ?>
        <?php foreach ($transactions as $date => $records): ?>
            <div class="date-section">
                <div class="date-title"><?php echo htmlspecialchars($date); ?></div>
                <table>
                    <thead>
                        <tr>
                            <th>訂單編號</th>
                            <th>商品名稱</th>
                            <th>作者</th>
                            <th>出貨地</th>
                            <th>數量</th>
                            <th>單價</th>
                            <th>小計</th>
                            <th>總金額</th>
                            <th>付款方式</th>
                            <th>購買者</th>
                            <th>賣家</th>
                            <th>交易時間</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($records as $record): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($record['order_id']); ?></td>
                                <td class="product-name">
                                    <a href="../product/detail.php?id=<?php echo htmlspecialchars($record['product_id']); ?>">
                                        <?php echo htmlspecialchars($record['product_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($record['author']); ?></td>
                                <td><?php echo htmlspecialchars($record['location']); ?></td>
                                <td><?php echo htmlspecialchars($record['quantity']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($record['price'], 2)); ?></td>
                                <td><?php echo htmlspecialchars(number_format($record['subtotal'], 2)); ?></td>
                                <td><?php echo htmlspecialchars(number_format($record['total_amount'], 2)); ?></td>
                                <td><?php echo htmlspecialchars($record['payment_method']); ?></td>
                                <td class="buyer-name">
                                    <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($record['buyer_id']); ?>">
                                        <?php echo htmlspecialchars($record['buyer_name']); ?>
                                    </a>
                                </td>
                                <td class="seller-name">
                                    <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($record['seller_id']); ?>">
                                        <?php echo htmlspecialchars($record['seller_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($record['created_at']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endforeach; ?>
    <?php else: ?>
        <p class="no-records">目前沒有交易紀錄。</p>
    <?php endif; ?>

    <?php mysqli_close($link); ?>
</body>
</html>