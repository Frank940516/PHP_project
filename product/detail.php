<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認商品 ID 是否存在
if (!isset($_GET['id'])) {
    echo "商品 ID 不存在！";
    exit();
}

$productId = $_GET['id'];

// 查詢商品詳細資訊
$sql = "SELECT p.id, p.name, p.price, p.stock, p.description, p.attachment, p.seller_id, 
               p.author, p.location, p.created_at, p.updated_at, 
               a.Name AS seller_name 
        FROM products p 
        JOIN accounts a ON p.seller_id = a.No 
        WHERE p.id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'i', $productId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$product = mysqli_fetch_assoc($result);

if (!$product) {
    echo "商品不存在！";
    exit();
}

$userId = null; // 預設為 null，表示未登入

if (isset($_SESSION['user'])) {
    $userEmail = $_SESSION['user'];

    // 查詢目前登入使用者的 ID
    $sqlUser = "SELECT No FROM accounts WHERE Email = ?";
    $stmtUser = mysqli_prepare($link, $sqlUser);
    mysqli_stmt_bind_param($stmtUser, 's', $userEmail);
    mysqli_stmt_execute($stmtUser);
    $resultUser = mysqli_stmt_get_result($stmtUser);
    $user = mysqli_fetch_assoc($resultUser);

    if ($user) {
        $userId = $user['No'];
    }
}
?>
<html>
    <meta charset="UTF-8">
    <head>
        <title><?php echo htmlspecialchars($product['name']); ?> - 商品詳情</title>
        <style>
            .top-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 20px;
                background-color: #f8f9fa;
                border-bottom: 1px solid #ddd;
            }
            .back-home-button {
                text-decoration: none;
                color: white;
                background-color: #007BFF;
                padding: 10px 15px;
                border-radius: 5px;
                font-size: 14px;
            }
            .back-home-button:hover {
                background-color: #0056b3;
            }
            .top-right-buttons {
                display: flex;
                gap: 10px;
            }
            .container {
                max-width: 1200px;
                margin: 0 auto;
                padding: 20px;
                font-family: Arial, sans-serif;
            }
            .product-detail {
                display: flex;
                gap: 20px;
                margin-bottom: 20px;
            }
            .product-image {
                flex: 1;
                max-width: 400px;
            }
            .product-image img {
                width: 100%;
                border: 1px solid #ccc;
                border-radius: 8px;
            }
            .product-info {
                flex: 2;
            }
            .product-info h1 {
                font-size: 24px;
                margin-bottom: 10px;
            }
            .product-info .price {
                font-size: 20px;
                color: #e74c3c;
                margin-bottom: 10px;
            }
            .product-info .stock {
                font-size: 16px;
                color: #2ecc71;
                margin-bottom: 10px;
            }
            .product-info .stock.sold-out {
                color: #e74c3c;
                font-weight: bold;
            }
            .product-info .description {
                margin-bottom: 20px;
                line-height: 1.5;
            }
            .product-info .seller {
                font-size: 16px;
                color: #555;
                margin-bottom: 10px;
            }
            .product-info .additional-info {
                font-size: 14px;
                color: #555;
                margin-bottom: 20px;
                line-height: 1.5;
            }
            .product-info .quantity {
                display: flex;
                align-items: center;
                gap: 10px;
                margin-bottom: 20px;
            }
            .product-info .quantity input {
                width: 50px;
                text-align: center;
            }
            .product-info button {
                padding: 10px 20px;
                background-color: #007BFF;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .product-info button:hover {
                background-color: #0056b3;
            }
        </style>
    </head>
    <body>
        <div class="top-bar">
            <a href="../index.php" class="back-home-button">返回首頁</a>
            <div class="top-right-buttons">
                <?php include('../userMenu.php'); ?>
            </div>
        </div>
        <div class="container">
            <div class="product-detail">
                <div class="product-image">
                    <img src="pic/<?php echo htmlspecialchars($product['attachment']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                </div>
                <div class="product-info">
                    <h1><?php echo htmlspecialchars($product['name']); ?></h1>
                    <p class="author">作者：<?php echo htmlspecialchars($product['author']); ?></p>
                    <p class="price">價格：$<?php echo htmlspecialchars($product['price']); ?></p>
                    <p class="stock <?php echo $product['stock'] == 0 ? 'sold-out' : ''; ?>">
                        <?php echo $product['stock'] == 0 ? '售完' : '庫存：' . htmlspecialchars($product['stock']) . ' 件'; ?>
                    </p>
                    <p class="seller">
                        賣家：<a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($product['seller_id']); ?>" style="text-decoration: underline; color: blue;">
                            <?php echo htmlspecialchars($product['seller_name']); ?>
                        </a>
                    </p>
                    <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <p class="additional-info">
                        出貨地：<?php echo htmlspecialchars($product['location']); ?><br>
                        創建日期：<?php echo htmlspecialchars($product['created_at']); ?><br>
                        更新日期：<?php echo htmlspecialchars($product['updated_at']); ?>
                    </p>
                    <?php if ($userId): ?>
                        <?php if ($product['seller_id'] == $userId): ?>
                            <!-- 如果是自己的商品，顯示編輯按鈕 -->
                            <form action="../product/edit.php" method="GET">
                                <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <button type="submit">編輯商品</button>
                            </form>
                        <?php elseif ($product['stock'] > 0): ?>
                            <!-- 如果不是自己的商品且有庫存，顯示購買功能 -->
                            <form action="../cart/addToCart.php" method="POST">
                                <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                                <div class="quantity">
                                    <label for="quantity">數量：</label>
                                    <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>">
                                </div>
                                <button type="submit">加入購物車</button>
                            </form>
                        <?php endif; ?>
                    <?php else: ?>
                        <p>請登入以進行購買或編輯操作。</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </body>
</html>