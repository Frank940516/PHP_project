<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

$userEmail = $_SESSION['user'];

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

$sqlCart = "SELECT c.product_id, p.name AS product_name, p.price, p.attachment, SUM(c.quantity) AS total_quantity, 
                   (p.price * SUM(c.quantity)) AS subtotal, p.stock, p.seller_id, a.Name AS seller_name
            FROM cart c
            JOIN products p ON c.product_id = p.id
            JOIN accounts a ON p.seller_id = a.No
            WHERE c.user_id = ?
            GROUP BY c.product_id, p.name, p.price, p.attachment, p.stock, p.seller_id, a.Name";
$stmtCart = mysqli_prepare($link, $sqlCart);
mysqli_stmt_bind_param($stmtCart, 'i', $userId);
mysqli_stmt_execute($stmtCart);
$resultCart = mysqli_stmt_get_result($stmtCart);

$cartItems = [];
$total = 0;

while ($row = mysqli_fetch_assoc($resultCart)) {
    if ($row['total_quantity'] > $row['stock']) {
        $updateCartSql = "UPDATE cart SET quantity = ? WHERE user_id = ? AND product_id = ?";
        $stmtUpdateCart = mysqli_prepare($link, $updateCartSql);
        $newQuantity = $row['stock'];
        mysqli_stmt_bind_param($stmtUpdateCart, 'iii', $newQuantity, $userId, $row['product_id']);
        mysqli_stmt_execute($stmtUpdateCart);

        $row['total_quantity'] = $newQuantity;
        $row['subtotal'] = $row['price'] * $newQuantity;
    }

    $cartItems[] = $row;
    $total += $row['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>購物車</title>
    <style>
        body { font-family: Arial, sans-serif; }
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
        th { background-color: #f2f2f2; }
        .total {
            font-weight: bold;
            text-align: right;
        }
        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
        }
        .checkout-btn {
            padding: 5px 10px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
        }
        .checkout-btn:hover {
            background-color: #0056b3;
        }
        .checkout-btn:disabled {
            background-color: #ccc;
            cursor: not-allowed;
        }
        .remove-link {
            display: block;
            margin-top: 5px;
        }
        .sold-out {
            color: red;
            font-weight: bold;
        }
        .checkout-all-btn {
            padding: 5px 10px;
            background-color: #28a745;
            color: white;
            border: none;
            border-radius: 3px;
            cursor: pointer;
            margin-left: 10px;
        }
        .checkout-all-btn:hover {
            background-color: #218838;
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

<h1>購物車</h1>
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
                <td>
                    <a href="../product/detail.php?id=<?php echo htmlspecialchars($item['product_id']); ?>" style="text-decoration: none; color: blue;">
                        <?php echo htmlspecialchars($item['product_name']); ?>
                    </a>
                    (庫存：<?php echo htmlspecialchars($item['stock']); ?>)
                </td>
                <td>
                    <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($item['seller_id']); ?>" style="text-decoration: none; color: blue;">
                        <?php echo htmlspecialchars($item['seller_name']); ?>
                    </a>
                </td>
                <td><?php echo htmlspecialchars($item['price']); ?></td>
                <td>
                    <?php if ($item['stock'] == 0): ?>
                        <span class="sold-out">已售完</span>
                    <?php else: ?>
                        <input 
                            type="number" 
                            class="quantity-input" 
                            data-product-id="<?php echo $item['product_id']; ?>" 
                            data-price="<?php echo $item['price']; ?>" 
                            value="<?php echo $item['total_quantity']; ?>" 
                            min="1" 
                            max="<?php echo $item['stock']; ?>" 
                            <?php echo $item['stock'] == 0 ? 'disabled' : ''; ?>
                        >
                    <?php endif; ?>
                </td>
                <td class="subtotal">
                    <?php echo htmlspecialchars($item['subtotal']); ?>
                </td>
                <td>
                    <?php if ($item['stock'] == 0): ?>
                        <span class="sold-out">無法結帳</span>
                    <?php else: ?>
                        <form class="checkout-form" method="POST" action="checkout.php">
                            <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                            <button type="submit" class="checkout-btn">結帳</button>
                        </form>
                    <?php endif; ?>
                    <a class="remove-link" href="delete.php?product_id=<?php echo $item['product_id']; ?>" onclick="return confirm('確定要移除此商品嗎？')">移除</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr><td colspan="7">購物車是空的</td></tr>
    <?php endif; ?>
    </tbody>
    <tfoot>
        <tr>
            <td colspan="5" class="total">總金額：</td>
            <td colspan="2" class="total-cell">
                <?php echo $total; ?>
                <form action="checkoutAll.php" method="POST" style="display: inline;">
                    <button type="submit" class="checkout-btn">全部結帳</button>
                </form>
            </td>
        </tr>
    </tfoot>
</table>

<script>
    document.querySelectorAll('.quantity-input').forEach(input => {
        input.addEventListener('change', async function () {
            const productId = this.dataset.productId;
            const price = parseFloat(this.dataset.price);
            const quantity = parseInt(this.value, 10);
            const max = parseInt(this.max, 10);

            // 檢查數量是否超過庫存
            if (quantity > max) {
                alert('數量不能超過庫存！');
                this.value = max;
                return;
            }

            // 更新小計
            const subtotalCell = this.closest('tr').querySelector('.subtotal');
            const subtotal = price * quantity;
            subtotalCell.textContent = subtotal; // 不進行格式化，直接顯示數據

            // 更新總金額
            updateTotal();

            // 同步更新到伺服器
            try {
                const response = await fetch('updateQuantity.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ product_id: productId, quantity: quantity })
                });

                const result = await response.json();
                if (!result.success) {
                    alert(result.message);
                }
            } catch (error) {
                console.error('更新數量失敗：', error);
                alert('更新數量失敗，請稍後再試！');
            }
        });
    });

    // 更新總金額
    function updateTotal() {
        let total = 0;
        document.querySelectorAll('.subtotal').forEach(subtotalCell => {
            total += parseFloat(subtotalCell.textContent);
        });

        // 確保正確更新總金額的顯示位置
        const totalCell = document.querySelector('.total-cell');
        if (totalCell) {
            totalCell.innerHTML = `
                ${total} <!-- 不進行格式化，直接顯示數據 -->
                <form action="checkoutAll.php" method="POST" style="display: inline;">
                    <button type="submit" class="checkout-btn">全部結帳</button>
                </form>
            `;
        }
    }
</script>

</body>
</html>
