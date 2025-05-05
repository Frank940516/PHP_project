<?php
// filepath: c:\xampp\htdocs\cart\checkout.php
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

// 獲取商品 ID
if (!isset($_POST['product_id'])) {
    echo "未選擇商品！";
    exit();
}

$productId = intval($_POST['product_id']);

// 查詢商品細節
$sqlProduct = "SELECT p.id, p.name, p.price, p.stock, p.description, p.attachment, c.quantity, 
                      a.Name AS seller_name  -- 新增賣家名稱
               FROM products p
               JOIN cart c ON p.id = c.product_id
               JOIN accounts a ON p.seller_id = a.No  -- 連接賣家帳戶
               WHERE c.user_id = ? AND p.id = ?";
$stmtProduct = mysqli_prepare($link, $sqlProduct);
mysqli_stmt_bind_param($stmtProduct, 'ii', $userId, $productId);
mysqli_stmt_execute($stmtProduct);
$resultProduct = mysqli_stmt_get_result($stmtProduct);
$product = mysqli_fetch_assoc($resultProduct);

if (!$product) {
    echo "商品不存在或已被移除！";
    exit();
}
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>結帳</title>
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
            width: 150px;
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
    <h1>結帳</h1>
    <table>
        <tr>
            <th>圖片</th>
            <th>商品名稱</th>
            <th>價格</th>
            <th>數量</th>
            <th>小計</th>
            <th>庫存</th>
            <th>描述</th>
            <th>賣家</th> <!-- 新增賣家欄位 -->
        </tr>
        <tr>
            <td>
                <img src="../product/pic/<?php echo htmlspecialchars($product['attachment']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>" class="product-image">
            </td>
            <td><?php echo htmlspecialchars($product['name']); ?></td>
            <td><?php echo htmlspecialchars($product['price']); ?></td>
            <td><?php echo htmlspecialchars($product['quantity']); ?></td>
            <td><?php echo htmlspecialchars($product['price'] * $product['quantity']); ?></td>
            <td><?php echo htmlspecialchars($product['stock']); ?></td>
            <td><?php echo htmlspecialchars($product['description']); ?></td>
            <td><?php echo htmlspecialchars($product['seller_name']); ?></td> <!-- 顯示賣家名稱 -->
        </tr>
    </table>

    <form action="checkoutCheck.php" method="POST">
        <input type="hidden" name="product_id" value="<?php echo $product['id']; ?>">
        <input type="hidden" name="quantity" value="<?php echo $product['quantity']; ?>">
        <button type="submit" class="checkout-btn">確認結帳</button>
    </form>
    <br>
    <a href="cart.php" class="back-link">返回購物車</a>
</body>
</html>