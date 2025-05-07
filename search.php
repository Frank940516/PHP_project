<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();

    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
    $searchKeyword = isset($_GET['search']) ? $_GET['search'] : '';
    $sortOrder = isset($_GET['sort']) ? $_GET['sort'] : 'date';
    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

    $sql = "SELECT id, name, price, attachment, category FROM products WHERE stock > 0";

    if (!empty($categoryFilter)) {
        $sql .= " AND category = '" . mysqli_real_escape_string($link, $categoryFilter) . "'";
    }

    if (!empty($searchKeyword)) {
        $sql .= " AND name LIKE '%" . mysqli_real_escape_string($link, $searchKeyword) . "%'";
    }

    if ($minPrice > 0) {
        $sql .= " AND price >= $minPrice";
    }

    if ($maxPrice > 0) {
        $sql .= " AND price <= $maxPrice";
    }

    if ($sortOrder === 'date') {
        $sql .= " ORDER BY created_at DESC";
    } elseif ($sortOrder === 'price_asc') {
        $sql .= " ORDER BY price ASC";
    } elseif ($sortOrder === 'price_desc') {
        $sql .= " ORDER BY price DESC";
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
        <title>搜尋結果</title>
        <style>
            .top-bar {
                display: flex;
                justify-content: space-between;
                align-items: center;
                padding: 10px 20px;
                background-color: #f8f9fa;
                border-bottom: 1px solid #ddd;
            }
            .home-button {
                background-color: #007BFF;
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
                text-decoration: none;
            }
            .home-button:hover {
                background-color: #0056b3;
            }
            .top-right-buttons {
                display: flex;
                gap: 10px;
            }
            .search-bar {
                text-align: center;
                margin: 20px 0;
            }
            .search-bar input[type="text"] {
                padding: 8px;
                width: 300px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .search-bar button {
                padding: 8px 15px;
                background-color: #007BFF;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .search-bar button:hover {
                background-color: #0056b3;
            }
            .filter-dropdown {
                position: relative;
                display: inline-block;
            }
            .filter-button {
                background-color: #007BFF;
                color: white;
                padding: 10px 15px;
                border: none;
                border-radius: 5px;
                font-size: 14px;
                cursor: pointer;
            }
            .filter-button:hover {
                background-color: #0056b3;
            }
            .filter-content {
                display: none;
                position: absolute;
                right: 0;
                background-color: white;
                box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                padding: 10px;
                border-radius: 5px;
                z-index: 1;
                width: 300px;
            }
            .filter-dropdown:hover .filter-content {
                display: block;
            }
            .filter-content label {
                display: block;
                margin-bottom: 5px;
                font-size: 14px;
                color: #333;
            }
            .filter-content input[type="radio"],
            .filter-content input[type="number"] {
                margin-bottom: 10px;
                width: 100%;
                padding: 5px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .filter-content button {
                background-color: #007BFF;
                color: white;
                padding: 8px 15px;
                border: none;
                border-radius: 4px;
                cursor: pointer;
                width: 100%;
            }
            .filter-content button:hover {
                background-color: #0056b3;
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
        </style>
    </head>
    <body>
        <!-- 頂部導航欄 -->
        <div class="top-bar">
            <a href="index.php" class="home-button">回首頁</a>
            <div class="top-right-buttons">
                <?php include('userMenu.php'); ?>
            </div>
        </div>

        <!-- 搜尋與篩選 -->
        <div style="text-align: center; margin: 20px 0;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                <!-- 搜尋欄 -->
                <form method="GET" action="search.php" style="display: flex; align-items: center;">
                    <input type="text" id="searchInput" name="search" placeholder="搜尋書籍" value="<?php echo htmlspecialchars($searchKeyword); ?>" style="padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px;">
                    <button type="submit" id="searchButton" style="padding: 8px 15px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;">搜尋</button>
                </form>

                <!-- 篩選條件 -->
                <div class="filter-dropdown">
                    <button class="filter-button">篩選條件 ▼</button>
                    <div class="filter-content">
                        <form method="GET" action="search.php">
                            <input type="hidden" name="search" value="<?php echo htmlspecialchars($searchKeyword); ?>">
                            <!-- 排序 -->
                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 14px; color: #333; margin-bottom: 5px; display: block;">排序</label>
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    <label style="display: flex; align-items: center; justify-content: space-between;">
                                        按日期排序
                                        <input type="radio" name="sort" value="date" <?php echo $sortOrder === 'date' ? 'checked' : ''; ?>>
                                    </label>
                                    <label style="display: flex; align-items: center; justify-content: space-between;">
                                        價格低到高
                                        <input type="radio" name="sort" value="price_asc" <?php echo $sortOrder === 'price_asc' ? 'checked' : ''; ?>>
                                    </label>
                                    <label style="display: flex; align-items: center; justify-content: space-between;">
                                        價格高到低
                                        <input type="radio" name="sort" value="price_desc" <?php echo $sortOrder === 'price_desc' ? 'checked' : ''; ?>>
                                    </label>
                                </div>
                            </div>

                            <!-- 價格範圍 -->
                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 14px; color: #333; margin-bottom: 5px; display: block;">價格範圍</label>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <label style="font-size: 14px; color: #333;">最低</label>
                                        <input type="number" name="min_price" placeholder="最低價格" value="<?php echo htmlspecialchars($minPrice); ?>" style="width: 100px; padding: 5px; border: 1px solid #ccc; border-radius: 4px;" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                    </div>
                                    <div style="display: flex; align-items: center; gap: 5px;">
                                        <label style="font-size: 14px; color: #333;">最高</label>
                                        <input type="number" name="max_price" placeholder="最高價格" value="<?php echo htmlspecialchars($maxPrice); ?>" style="width: 100px; padding: 5px; border: 1px solid #ccc; border-radius: 4px;" min="0" oninput="this.value = this.value.replace(/[^0-9]/g, '');">
                                    </div>
                                </div>
                            </div>

                            <!-- 按鈕 -->
                            <div style="display: flex; gap: 10px;">
                                <button type="submit" style="flex: 1; background-color: #007BFF; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">套用篩選</button>
                                <button type="button" onclick="window.location.href='search.php?search=<?php echo urlencode($searchKeyword); ?>&sort=date';" style="flex: 1; background-color: #6c757d; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">重設篩選</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 搜尋結果 -->
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
                <p style="text-align: center;">沒有符合條件的商品。</p>
            <?php endif; ?>
        </div>
    </body>
</html>