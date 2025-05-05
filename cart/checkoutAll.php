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

// 查詢購物車中的所有商品
$sqlCart = "SELECT c.product_id, p.name AS product_name, p.price, p.attachment, c.quantity, 
                   (p.price * c.quantity) AS subtotal, p.stock
            FROM cart c
            JOIN products p ON c.product_id = p.id
            WHERE c.user_id = ?";
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
    <title>全部結帳</title>
    <style>
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
        .product-image {
            width: 100px;
            height: auto;
        }
        .checkout-btn {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .checkout-btn:hover {
            background-color: #0056b3;
        }
        .back-link {
            text-decoration: none;
            color: #007BFF;
        }
        .back-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <h1>全部結帳</h1>
    <?php if (!empty($cartItems)): ?>
        <table>
            <thead>
                <tr>
                    <th>圖片</th>
                    <th>商品名稱</th>
                    <th>價格</th>
                    <th>數量</th>
                    <th>小計</th>
                    <th>庫存</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cartItems as $item): ?>
                    <tr>
                        <td>
                            <img src="../product/pic/<?php echo htmlspecialchars($item['attachment']); ?>" alt="<?php echo htmlspecialchars($item['product_name']); ?>" class="product-image">
                        </td>
                        <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($item['price']); ?></td>
                        <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                        <td><?php echo htmlspecialchars($item['subtotal']); ?></td>
                        <td><?php echo htmlspecialchars($item['stock']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right;">總金額：</td>
                    <td colspan="2"><?php echo htmlspecialchars($total); ?></td>
                </tr>
            </tfoot>
        </table>
        <form action="checkoutCheck.php" method="POST">
            <?php foreach ($cartItems as $index => $item): ?>
                <input type="hidden" name="products[<?php echo $index; ?>][product_id]" value="<?php echo $item['product_id']; ?>">
                <input type="hidden" name="products[<?php echo $index; ?>][quantity]" value="<?php echo $item['quantity']; ?>">
            <?php endforeach; ?>
            <button type="submit" class="checkout-btn">確認結帳</button>
        </form>
    <?php else: ?>
        <p>購物車是空的。</p>
    <?php endif; ?>
    <br>
    <a href="cart.php" class="back-link">返回購物車</a>
</body>
</html>