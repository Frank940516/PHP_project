<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 確認賣家 ID 是否存在
if (!isset($_GET['seller_id'])) {
    echo "賣家 ID 不存在！";
    exit();
}

$sellerId = $_GET['seller_id'];

// 查詢賣家名稱
$sqlSeller = "SELECT Name FROM accounts WHERE No = ?";
$stmtSeller = mysqli_prepare($link, $sqlSeller);
mysqli_stmt_bind_param($stmtSeller, 'i', $sellerId);
mysqli_stmt_execute($stmtSeller);
$resultSeller = mysqli_stmt_get_result($stmtSeller);
$seller = mysqli_fetch_assoc($resultSeller);

if (!$seller) {
    echo "賣家不存在！";
    exit();
}

// 查詢賣家販售的商品
$sqlProducts = "SELECT id, name, price, stock, attachment FROM products WHERE seller_id = ? AND stock > 0";
$stmtProducts = mysqli_prepare($link, $sqlProducts);
mysqli_stmt_bind_param($stmtProducts, 'i', $sellerId);
mysqli_stmt_execute($stmtProducts);
$resultProducts = mysqli_stmt_get_result($stmtProducts);
$products = [];
while ($row = mysqli_fetch_assoc($resultProducts)) {
    $products[] = $row;
}
?>
<html>
    <meta charset="UTF-8">
    <head>
        <title><?php echo htmlspecialchars($seller['Name']); ?> 的商場</title>
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
            .product-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
                gap: 20px;
                padding: 20px;
            }
            .product-item {
                border: 1px solid #ccc;
                border-radius: 8px;
                overflow: hidden;
                text-align: center;
                background-color: #fff;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            }
            .product-item img {
                width: 100%;
                height: 150px;
                object-fit: cover;
            }
            .product-item h3 {
                font-size: 16px;
                margin: 10px 0;
                color: #333;
            }
            .product-item p {
                font-size: 14px;
                color: #666;
                margin: 5px 0;
            }
            .product-item .price {
                font-size: 18px;
                color: #e74c3c;
                font-weight: bold;
                margin: 10px 0;
            }
            .product-item a {
                text-decoration: underline;
                color: blue;
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
        <h1 style="text-align: center;"><?php echo htmlspecialchars($seller['Name']); ?> 的商場</h1>
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-item">
                        <img src="../product/pic/<?php echo htmlspecialchars($product['attachment']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3>
                            <a href="../product/detail.php?id=<?php echo $product['id']; ?>">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <p class="price">$<?php echo htmlspecialchars($product['price']); ?></p>
                        <?php if ($product['stock'] == 0): ?>
                            <p style="color: red; font-weight: bold;">售完</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center;">該賣家目前沒有商品。</p>
            <?php endif; ?>
        </div>
    </body>
</html>