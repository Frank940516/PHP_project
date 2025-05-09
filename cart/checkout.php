<!-- checkout.php -->
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

// 查詢使用者可用的優惠券
$sqlUserCoupons = "
    SELECT c.code, c.discount, c.discount_type
      FROM user_coupons uc
      JOIN coupons c ON uc.coupon_id = c.id
     WHERE uc.user_id = ? 
       AND uc.is_used = 0
       AND c.is_active = 1 
       AND c.expiration_date >= CURDATE()
       AND c.start_date <= CURDATE()
";
$stmtUserCoupons = mysqli_prepare($link, $sqlUserCoupons);
mysqli_stmt_bind_param($stmtUserCoupons, 'i', $userId);
mysqli_stmt_execute($stmtUserCoupons);
$resultUserCoupons = mysqli_stmt_get_result($stmtUserCoupons);

$availableCoupons = [];
while ($row = mysqli_fetch_assoc($resultUserCoupons)) {
    $availableCoupons[] = $row;
}

// 取得 productId
if (!isset($_POST['product_id']) && !isset($productId)) {
    echo "未選擇商品！";
    exit();
}
$productId = isset($_POST['product_id']) ? intval($_POST['product_id']) : $productId;

// 查詢商品細節
$sqlProduct = "
    SELECT p.id, p.name, p.price, p.stock, p.description, p.attachment, p.category, p.author, p.location,
           c.quantity, a.Name AS seller_name
      FROM products p
      JOIN cart c ON p.id = c.product_id
      JOIN accounts a ON p.seller_id = a.No
     WHERE c.user_id = ? 
       AND p.id = ?
";
$stmtProduct = mysqli_prepare($link, $sqlProduct);
mysqli_stmt_bind_param($stmtProduct, 'ii', $userId, $productId);
mysqli_stmt_execute($stmtProduct);
$resultProduct = mysqli_stmt_get_result($stmtProduct);
$product = mysqli_fetch_assoc($resultProduct);
if (!$product) {
    echo "商品不存在或已被移除！";
    exit();
}

// 處理優惠券套用
$discountedPrice = null;
$couponMessage = '';
$selectedCoupon = $_POST['selected_coupon'] ?? '';
if ($selectedCoupon !== '') {
    foreach ($availableCoupons as $coupon) {
        if ($coupon['code'] === $selectedCoupon) {
            $base = $product['price'] * $product['quantity'];
            if ($coupon['discount_type'] === 'percentage') {
                $discountedPrice = $base * (1 - $coupon['discount'] / 100);
            } else {
                $discountedPrice = $base - $coupon['discount'];
            }
            // 四捨五入並確保不為負
            $discountedPrice = max(round($discountedPrice), 0);
            $couponMessage = '<span style="color:green;">優惠券已套用！</span>';
            break;
        }
    }
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
        .original-price {
            text-decoration: line-through;
            color: red;
        }
        .discounted-price {
            color: green;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <h1>結帳</h1>

    <table>
        <thead>
            <tr>
                <th>圖片</th>
                <th>書名</th>
                <th>價格</th>
                <th>數量</th>
                <th>小計</th>
                <th>庫存</th>
                <th>描述</th>
                <th>種類</th>
                <th>賣家</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>
                    <img src="../product/pic/<?php echo htmlspecialchars($product['attachment']); ?>"
                         alt="<?php echo htmlspecialchars($product['name']); ?>"
                         style="width: 100px; height: auto;">
                </td>
                <td><?php echo htmlspecialchars($product['name']); ?></td>
                <td>
                    <?php if ($discountedPrice !== null): ?>
                        <span class="original-price"><?php echo htmlspecialchars($product['price']); ?></span>
                        <span class="discounted-price"><?php echo $discountedPrice; ?></span>
                    <?php else: ?>
                        <?php echo htmlspecialchars($product['price']); ?>
                    <?php endif; ?>
                </td>
                <td><?php echo htmlspecialchars($product['quantity']); ?></td>
                <td><?php echo $discountedPrice !== null ? $discountedPrice : $product['price'] * $product['quantity']; ?></td>
                <td><?php echo htmlspecialchars($product['stock']); ?></td>
                <td><?php echo htmlspecialchars($product['description']); ?></td>
                <td><?php echo htmlspecialchars($product['category']); ?></td>
                <td><?php echo htmlspecialchars($product['seller_name']); ?></td>
            </tr>
        </tbody>
    </table>

    <!-- 優惠券選擇 -->
    <form method="POST">
        <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($productId); ?>">
        <label for="selected_coupon">選擇優惠券：</label>
        <select id="selected_coupon" name="selected_coupon" onchange="this.form.submit()">
            <option value="">不使用優惠券</option>
            <?php foreach ($availableCoupons as $coupon): ?>
                <option value="<?php echo htmlspecialchars($coupon['code']); ?>"
                    <?php echo $selectedCoupon === $coupon['code'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($coupon['code']); ?> -
                    <?php echo $coupon['discount_type'] === 'percentage'
                        ? htmlspecialchars($coupon['discount']) . '% 折扣' 
                        : '$' . htmlspecialchars($coupon['discount']) . ' 折扣'; ?>
                </option>
            <?php endforeach; ?>
        </select>
    </form>
    <div><?php echo $couponMessage; ?></div>

    <!-- 最終結帳表單 -->
    <form action="checkoutCheck.php" method="POST">
        <input type="hidden" name="product_id"      value="<?php echo htmlspecialchars($product['id']); ?>">
        <input type="hidden" name="quantity"        value="<?php echo htmlspecialchars($product['quantity']); ?>">
        <input type="hidden" name="selected_coupon" value="<?php echo htmlspecialchars($selectedCoupon); ?>">

        <!-- 新增付款方式 -->
        <div class="payment-method">
            <h3>選擇付款方式</h3>
            <label>
                <input type="radio" name="payment_method" value="credit_card" required> 信用卡
            </label>
            <label>
                <input type="radio" name="payment_method" value="paypal"> PayPal
            </label>
            <label>
                <input type="radio" name="payment_method" value="bank_transfer"> 銀行轉帳
            </label>
            <label>
                <input type="radio" name="payment_method" value="cash_on_delivery"> 貨到付款
            </label>
        </div>

        <button type="submit" class="checkout-btn">確認結帳</button>
    </form>

    <br>
    <a href="cart.php" class="back-link">返回購物車</a>
</body>
</html>
