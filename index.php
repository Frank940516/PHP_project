<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();

    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
    $searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
    $selectedCategory = isset($_GET['category']) ? $_GET['category'] : '';

    // 定義類別陣列
    $categories = [
        "文學/小說", "心理勵志", "商業/理財", "藝術/設計", "人文/歷史/地理",
        "科學/科普/自然", "電腦/資訊", "語言學習", "考試用書/教科書",
        "童書/繪本", "漫畫/輕小說", "旅遊/地圖", "醫療/保健", "生活風格/休閒", "其他"
    ];

    $sql = "SELECT id, name, price, attachment, category FROM products WHERE stock > 0";

    if (!empty($categoryFilter)) {
        $sql .= " AND category = '" . mysqli_real_escape_string($link, $categoryFilter) . "'";
    }

    if (!empty($searchKeyword)) {
        $sql .= " AND name LIKE '%" . mysqli_real_escape_string($link, $searchKeyword) . "%'";
    }

    $result = mysqli_query($link, $sql);
    $products = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $products[] = $row;
    }
?>
<html>
    <meta charset="UTF-8">
    <head>
        <title>二手書交易平台-首頁</title>
        <style>
            .top-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 20px;
                background-color: #f8f9fa;
                border-bottom: 1px solid #ddd;
            }
            .announcement-button {
                background-color: #007BFF;
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                text-decoration: none;
            }
            .announcement-button:hover {
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
                display: inline-block;
                margin: 10px 0;
                padding: 8px 15px;
                background-color: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-size: 14px;
            }
            .product-item a:hover {
                background-color: #45a049;
            }
        </style>
    </head>
    <body>
        <div class="top-bar">
            <a href="announcement/announcement.php" class="announcement-button">公告</a>
            <div class="top-right-buttons">
                <?php include('userMenu.php'); ?>
            </div>
        </div>
        <h1 style="text-align: center;">二手書交易平台</h1>
        <div style="text-align: center; margin: 20px 0;">
            <!-- 搜尋欄 -->
            <form method="GET" action="index.php" style="margin-bottom: 20px;">
                <input type="text" name="search" placeholder="搜尋書籍" value="<?php echo htmlspecialchars($searchKeyword); ?>" style="padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px;">
                <button type="submit" style="padding: 8px 15px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer;">搜尋</button>
            </form>

            <?php if (empty($searchKeyword)): // 當搜尋欄為空時顯示類別按鈕 ?>
                <!-- 類別按鈕 -->
                <div>
                    <!-- 第一行按鈕 -->
                    <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                        <?php $firstRow = array_slice($categories, 0, 7); // 取前 7 個 ?>
                        <?php foreach ($firstRow as $category): ?>
                            <a href="index.php?category=<?php echo $selectedCategory === $category ? '' : urlencode($category); ?>" 
                               style="flex: 1; margin: 5px; width: 80px; height: 80px; border-radius: 50%; background-color: <?php echo $selectedCategory === $category ? '#007BFF' : '#f0f0f0'; ?>; text-decoration: none; color: <?php echo $selectedCategory === $category ? 'white' : '#333'; ?>; font-size: 16px; text-align: center; display: flex; align-items: center; justify-content: center;">
                                <?php echo htmlspecialchars($category); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>

                    <!-- 第二行按鈕 -->
                    <div style="display: flex; justify-content: space-between;">
                        <?php $secondRow = array_slice($categories, 7); // 取後 8 個 ?>
                        <?php foreach ($secondRow as $category): ?>
                            <a href="index.php?category=<?php echo $selectedCategory === $category ? '' : urlencode($category); ?>" 
                               style="flex: 1; margin: 5px; width: 80px; height: 80px; border-radius: 50%; background-color: <?php echo $selectedCategory === $category ? '#007BFF' : '#f0f0f0'; ?>; text-decoration: none; color: <?php echo $selectedCategory === $category ? 'white' : '#333'; ?>; font-size: 16px; text-align: center; display: flex; align-items: center; justify-content: center;">
                                <?php echo htmlspecialchars($category); ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-item">
                        <img src="product/pic/<?php echo htmlspecialchars($product['attachment']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3>
                            <a href="product/detail.php?id=<?php echo $product['id']; ?>" style="text-decoration: underline; color: blue;">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <p class="price">$<?php echo htmlspecialchars($product['price']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center;">目前沒有符合條件的商品。</p>
            <?php endif; ?>
        </div>
    </body>
</html>