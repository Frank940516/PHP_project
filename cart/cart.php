<?php
require('../db.inc'); // 資料庫連線檔案
mysqli_set_charset($link, 'utf8');
session_start();

// 檢查是否登入
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

// 查詢購物車內容，並整合同件商品（同賣家）
$sqlCart = "SELECT c.product_id, p.name AS product_name, p.price, p.attachment, SUM(c.quantity) AS total_quantity, 
                   (p.price * SUM(c.quantity)) AS subtotal, a.Name AS seller_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            JOIN accounts a ON p.seller_id = a.No
            WHERE c.user_id = ?
            GROUP BY c.product_id, p.name, p.price, p.attachment, a.Name";
$stmtCart = mysqli_prepare($link, $sqlCart);
mysqli_stmt_bind_param($stmtCart, 'i', $userId);
mysqli_stmt_execute($stmtCart);
$resultCart = mysqli_stmt_get_result($stmtCart);

$cartItems = [];
$total = 0;

while ($row = mysqli_fetch_assoc($resultCart)) {
    $cartItems[] = $row;
    $total += $row['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>訂單管理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .back-home-button input {
            padding: 5px 10px;
            font-size: 14px;
            cursor: pointer;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .total {
            font-weight: bold;
            text-align: right;
        }
        .product-image {
            width: 100x;
            height: 100px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Top bar with back home button and user menu -->
    <div class="top-bar">
        <div class="back-home-button">
            <input type="button" value="返回首頁" onclick="location.href='../index.php'">
        </div>
        <?php require('../userMenu.php'); ?>
    </div>

    <h1>訂單管理</h1>
    <table>
        <thead>
            <tr>
                <th>圖片</th>
                <th>商品名稱</th>
                <th>賣家</th>
                <th>價格</th>
                <th>數量</th>
                <th>小計</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($cartItems)): ?>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <img src="../product/pic/<?php echo htmlspecialchars($item['attachment']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image">
                        </td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['seller_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['price']); ?></td>
                        <td><?php echo htmlspecialchars($item['total_quantity']); ?></td>
                        <td><?php echo htmlspecialchars($item['subtotal']); ?></td>
                        <td>
                            <a href="delete.php?product_id=<?php echo $item['product_id']; ?>">刪除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">購物車是空的</td>
                </tr>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5" class="total">總金額：</td>
                <td colspan="2"><?php echo $total; ?></td>
            </tr>
        </tfoot>
    </table>
</body>
</html>