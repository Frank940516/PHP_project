<?php
require('../db.inc'); // 資料庫連線檔案
require('../authCheck.php');
mysqli_set_charset($link, 'utf8');

// 檢查是否登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// 從資料庫取出商品資料
$query = "SELECT id, name, author, price, stock, condition, category, description, image_path FROM products ORDER BY id DESC";
$result = mysqli_query($link, $query);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>商品列表</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .product {
            border: 1px solid #ccc;
            border-radius: 4px;
            padding: 16px;
            margin-bottom: 16px;
            display: flex;
            align-items: center;
        }
        .product img {
            max-width: 120px;
            max-height: 120px;
            margin-right: 16px;
        }
        .product-details {
            flex: 1;
        }
        .product-details h2 {
            margin: 0;
            font-size: 18px;
        }
        .product-details p {
            margin: 6px 0;
        }
        .product-details .price {
            font-weight: bold;
            color: #007BFF;
        }
    </style>
</head>
<body>
    <h1>商品列表</h1>
    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <div class="product">
                <?php if (!empty($row['image_path'])): ?>
                    <img src="<?php echo htmlspecialchars($row['image_path']); ?>" alt="商品圖片">
                <?php endif; ?>
                <div class="product-details">
                    <h2><?php echo htmlspecialchars($row['name']); ?></h2>
                    <p>作者：<?php echo htmlspecialchars($row['author']); ?></p>
                    <p class="price">價格：NT$<?php echo htmlspecialchars($row['price']); ?></p>
                    <p>庫存：<?php echo htmlspecialchars($row['stock']); ?></p>
                    <p>商品狀況：<?php echo htmlspecialchars($row['condition']); ?></p>
                    <p>書籍種類：<?php echo htmlspecialchars($row['category']); ?></p>
                    <p>描述：<?php echo nl2br(htmlspecialchars($row['description'])); ?></p>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>目前沒有商品。</p>
    <?php endif; ?>
</body>
</html>