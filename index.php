<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();

    // 定義類別陣列
    $categories = [
        "文學/小說", "心理勵志", "商業/理財", "藝術/設計", "人文/歷史/地理",
        "科學/科普/自然", "電腦/資訊", "語言學習", "考試用書/教科書",
        "童書/繪本", "漫畫/輕小說", "旅遊/地圖", "醫療/保健", "生活風格/休閒", "其他"
    ];

     // 分類對應圖片（請將路徑補上正確的圖檔）
    $categoryImages = [
        "文學/小說" => "category_icons/literature.jpg",
        "心理勵志" => "category_icons/psychology.jpg",
        "商業/理財" => "category_icons/business.jpg",
        "藝術/設計" => "category_icons/art.jpg",
        "人文/歷史/地理" => "category_icons/humanity.jpg",
        "科學/科普/自然" => "category_icons/science.jpg",
        "電腦/資訊" => "category_icons/computer.jpg",
        "語言學習" => "category_icons/language.jpg",
        "考試用書/教科書" => "category_icons/exam.jpg",
        "童書/繪本" => "category_icons/children.jpg",
        "漫畫/輕小說" => "category_icons/comic.jpg",
        "旅遊/地圖" => "category_icons/travel.jpg",
        "醫療/保健" => "category_icons/medical.jpg",
        "生活風格/休閒" => "category_icons/life.jpg",
        "其他" => "category_icons/other.jpg"
    ];

    // 取得篩選條件
    $categoryFilter = isset($_GET['category']) ? $_GET['category'] : '';
    $sortOrder = isset($_GET['sort']) ? $_GET['sort'] : '';
    $minPrice = isset($_GET['min_price']) ? (float)$_GET['min_price'] : 0;
    $maxPrice = isset($_GET['max_price']) ? (float)$_GET['max_price'] : 0;

    // 查詢所有正在販售的商品，並根據篩選條件篩選
    $sql = "SELECT id, name, price, attachment, category, created_at FROM products WHERE stock > 0";

    if (!empty($categoryFilter)) {
        $sql .= " AND category = '" . mysqli_real_escape_string($link, $categoryFilter) . "'";
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
            .filter-dropdown {
                position: relative;
                display: inline-block;
                z-index: 100;
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
            .product-item a {
                display: block;           /* 讓 a 變成區塊元素 */
                width: 100%;              /* 寬度佔滿父層 */
                margin: 10px 0;
                padding: 8px 15px;
                background-color: #4CAF50;
                color: white;
                text-decoration: none;
                border-radius: 4px;
                font-size: 14px;
                box-sizing: border-box;   /* 讓 padding 不會超出寬度 */
                text-align: center;       /* 文字置中 */
                transition: background 0.2s;
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

        <!-- 搜尋與篩選 -->
        <div style="text-align: center; margin: 20px 0;">
            <div style="display: flex; justify-content: center; align-items: center; gap: 20px;">
                <!-- 搜尋欄 -->
                <form method="GET" action="search.php" style="display: flex; align-items: center;">
                    <input type="text" id="searchInput" name="search" placeholder="搜尋書籍" style="padding: 8px; width: 300px; border: 1px solid #ccc; border-radius: 4px;" oninput="toggleSearchButton()">
                    <input type="hidden" name="sort" value="<?php echo htmlspecialchars($sortOrder); ?>">
                    <input type="hidden" name="min_price" value="<?php echo htmlspecialchars($minPrice); ?>">
                    <input type="hidden" name="max_price" value="<?php echo htmlspecialchars($maxPrice); ?>">
                    <button type="submit" id="searchButton" style="padding: 8px 15px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer; margin-left: 10px;" disabled>搜尋</button>
                </form>

                <script>
                    function toggleSearchButton() {
                        const searchInput = document.getElementById('searchInput');
                        const searchButton = document.getElementById('searchButton');
                        searchButton.disabled = searchInput.value.trim() === '';
                    }
                </script>

                <!-- 篩選條件 -->
                <div class="filter-dropdown">
                    <button class="filter-button">篩選條件 ▼</button>
                    <div class="filter-content">
                        <form method="GET" action="index.php">
                            <!-- 排序 -->
                            <div style="margin-bottom: 10px;">
                                <label style="font-size: 14px; color: #333; margin-bottom: 5px; display: block;">排序</label>
                                <div style="display: flex; flex-direction: column; gap: 5px;">
                                    <label style="display: flex; align-items: center; justify-content: space-between;">
                                        按日期排序
                                        <input type="radio" name="sort" value="date" <?php echo empty($sortOrder) || $sortOrder === 'date' ? 'checked' : ''; ?>>
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
                                <button type="button" onclick="window.location.href='index.php?sort=date';" style="flex: 1; background-color: #6c757d; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">重設篩選</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- 類別按鈕 -->
        <div>
            <div>
            <!-- 第一行按鈕 -->
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
                <?php $firstRow = array_slice($categories, 0, 7); // 取前 7 個 ?>
                <?php foreach ($firstRow as $category): ?>
                    <a href="index.php?category=<?php echo ($categoryFilter === $category) ? '' : urlencode($category); ?>" 
                    style="flex: 1; margin: 5px; width: 80px; height: 80px; border-radius: 50%; background-color: <?php echo ($categoryFilter === $category) ? '#007BFF' : '#f0f0f0'; ?>; text-decoration: none; color: <?php echo ($categoryFilter === $category) ? 'white' : '#333'; ?>; font-size: 16px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        <div style="width:80px; height:80px; border-radius:50%; overflow:hidden; position:relative; margin-bottom:5px;">
                            <img src="<?php echo htmlspecialchars($categoryImages[$category]); ?>"
                                alt="<?php echo htmlspecialchars($category); ?>"
                                style="width:100%; height:100%; object-fit:cover; position:absolute; left:0; top:0;">
                        </div>
                        <span style="position: relative; z-index: 1;"><?php echo htmlspecialchars($category); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

            <!-- 第二行按鈕 -->
            <div style="display: flex; justify-content: space-between;">
                <?php $secondRow = array_slice($categories, 7); // 取後 8 個 ?>
                <?php foreach ($secondRow as $category): ?>
                    <a href="index.php?category=<?php echo ($categoryFilter === $category) ? '' : urlencode($category); ?>" 
                    style="flex: 1; margin: 5px; width: 80px; height: 80px; border-radius: 50%; background-color: <?php echo ($categoryFilter === $category) ? '#007BFF' : '#f0f0f0'; ?>; text-decoration: none; color: <?php echo ($categoryFilter === $category) ? 'white' : '#333'; ?>; font-size: 16px; text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center; position: relative; overflow: hidden;">
                        <div style="width:80px; height:80px; border-radius:50%; overflow:hidden; position:relative; margin-bottom:5px;">
                            <img src="<?php echo htmlspecialchars($categoryImages[$category]); ?>"
                                alt="<?php echo htmlspecialchars($category); ?>"
                                style="width:100%; height:100%; object-fit:cover; position:absolute; left:0; top:0;">
                        </div>
                        <span style="position: relative; z-index: 1;"><?php echo htmlspecialchars($category); ?></span>
                    </a>
                <?php endforeach; ?>
            </div>

        <!-- 商品列表 -->
        <div class="product-grid">
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <div class="product-item">
                        <img src="product/pic/<?php echo htmlspecialchars($product['attachment']); ?>" alt="<?php echo htmlspecialchars($product['name']); ?>">
                        <h3>
                            <a href="product/detail.php?id=<?php echo $product['id']; ?>" style="color: blue; text-decoration: none;">
                                <?php echo htmlspecialchars($product['name']); ?>
                            </a>
                        </h3>
                        <p class="price">$<?php echo htmlspecialchars($product['price']); ?></p>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center;">目前沒有商品。</p>
            <?php endif; ?>
        </div>
    </body>
</html>