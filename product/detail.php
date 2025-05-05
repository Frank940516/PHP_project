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
$sql = "SELECT p.id, p.name, p.price, p.stock, p.description, p.attachment, p.seller_id, a.Name AS seller_name 
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
            .product-info .description {
                margin-bottom: 20px;
                line-height: 1.5;
            }
            .product-info .seller {
                font-size: 16px;
                color: #555;
                margin-bottom: 10px;
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
                    <p class="price">價格：$<?php echo htmlspecialchars($product['price']); ?></p>
                    <p class="stock">庫存：<?php echo htmlspecialchars($product['stock']); ?> 件</p>
                    <p class="seller">
                        賣家：<a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($product['seller_id']); ?>" style="text-decoration: underline; color: blue;">
                            <?php echo htmlspecialchars($product['seller_name']); ?>
                        </a>
                    </p>
                    <p class="description"><?php echo nl2br(htmlspecialchars($product['description'])); ?></p>
                    <form action="../cart/addToCart.php" method="POST">
                    <input type="hidden" name="product_id" value="<?php echo htmlspecialchars($product['id']); ?>">
                        <div class="quantity">
                            <label for="quantity">數量：</label>
                            <input type="number" id="quantity" name="quantity" value="1" min="1" max="<?php echo htmlspecialchars($product['stock']); ?>">
                        </div>
                        <button type="submit">加入購物車</button>
                    </form>
                </div>
            </div>
        </div>
    </body>
</html>