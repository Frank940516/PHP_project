<?php
require('../db.inc'); // 資料庫連線檔案
mysqli_set_charset($link, 'utf8');
session_start();

// 檢查是否登入
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

// 確認商品 ID
if (!isset($_GET['id'])) {
    echo "商品 ID 不存在！";
    exit();
}

$productId = $_GET['id'];

// 查詢商品資料
$sqlProduct = "SELECT id, name, price, stock, `condition`, description, attachment, category FROM products WHERE id = ? AND seller_id = ?";
$stmtProduct = mysqli_prepare($link, $sqlProduct);
mysqli_stmt_bind_param($stmtProduct, 'ii', $productId, $userId);
mysqli_stmt_execute($stmtProduct);
$resultProduct = mysqli_stmt_get_result($stmtProduct);
$product = mysqli_fetch_assoc($resultProduct);

if (!$product) {
    echo "商品不存在或無權限編輯！";
    exit();
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>編輯商品</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
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
        form {
            max-width: 600px;
            margin: 0 auto;
        }
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: bold;
        }
        input, textarea, select {
            width: 100%;
            padding: 8px;
            margin-bottom: 16px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        button {
            padding: 10px 20px;
            background-color: #007BFF;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        button:hover {
            background-color: #0056b3;
        }
        .current-image {
            max-width: 100px;
            max-height: 100px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Top bar with back to orders button and user menu -->
    <div class="top-bar">
        <div class="back-home-button">
            <input type="button" value="返回訂單" onclick="location.href='showList.php'">
        </div>
        <?php require('../userMenu.php'); ?>
    </div>

    <h1>編輯商品</h1>
    <form method="POST" action="update.php" enctype="multipart/form-data">
        <input type="hidden" name="id" value="<?php echo htmlspecialchars($product['id']); ?>">

        <label for="name">商品名稱</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($product['name']); ?>" required>

        <label for="price">價格</label>
        <input type="number" id="price" name="price" step="1" min="1" value="<?php echo htmlspecialchars($product['price']); ?>" required oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '');">

        <label for="stock">庫存</label>
        <input type="number" id="stock" name="stock" step="1" min="1" value="<?php echo htmlspecialchars($product['stock']); ?>" required oninput="this.value = this.value.replace(/[^0-9]/g, '').replace(/^0+/, '');">

        <label for="condition">商品狀況</label>
        <select id="condition" name="condition" required>
            <option value="全新" <?php echo $product['condition'] === '全新' ? 'selected' : ''; ?>>全新</option>
            <option value="九成新" <?php echo $product['condition'] === '九成新' ? 'selected' : ''; ?>>九成新</option>
            <option value="七成新" <?php echo $product['condition'] === '七成新' ? 'selected' : ''; ?>>七成新</option>
            <option value="五成新" <?php echo $product['condition'] === '五成新' ? 'selected' : ''; ?>>五成新</option>
        </select>

        <label for="category">書籍種類</label>
        <select id="category" name="category" required>
            <option value="文學/小說" <?php echo $product['category'] === '文學/小說' ? 'selected' : ''; ?>>文學/小說</option>
            <option value="心理勵志" <?php echo $product['category'] === '心理勵志' ? 'selected' : ''; ?>>心理勵志</option>
            <option value="商業/理財" <?php echo $product['category'] === '商業/理財' ? 'selected' : ''; ?>>商業/理財</option>
            <option value="藝術/設計" <?php echo $product['category'] === '藝術/設計' ? 'selected' : ''; ?>>藝術/設計</option>
            <option value="人文/歷史/地理" <?php echo $product['category'] === '人文/歷史/地理' ? 'selected' : ''; ?>>人文/歷史/地理</option>
            <option value="科學/科普/自然" <?php echo $product['category'] === '科學/科普/自然' ? 'selected' : ''; ?>>科學/科普/自然</option>
            <option value="電腦/資訊" <?php echo $product['category'] === '電腦/資訊' ? 'selected' : ''; ?>>電腦/資訊</option>
            <option value="語言學習" <?php echo $product['category'] === '語言學習' ? 'selected' : ''; ?>>語言學習</option>
            <option value="考試用書/教科書" <?php echo $product['category'] === '考試用書/教科書' ? 'selected' : ''; ?>>考試用書/教科書</option>
            <option value="童書/繪本" <?php echo $product['category'] === '童書/繪本' ? 'selected' : ''; ?>>童書/繪本</option>
            <option value="漫畫/輕小說" <?php echo $product['category'] === '漫畫/輕小說' ? 'selected' : ''; ?>>漫畫/輕小說</option>
            <option value="旅遊/地圖" <?php echo $product['category'] === '旅遊/地圖' ? 'selected' : ''; ?>>旅遊/地圖</option>
            <option value="醫療/保健" <?php echo $product['category'] === '醫療/保健' ? 'selected' : ''; ?>>醫療/保健</option>
            <option value="生活風格/休閒" <?php echo $product['category'] === '生活風格/休閒' ? 'selected' : ''; ?>>生活風格/休閒</option>
            <option value="其他" <?php echo $product['category'] === '其他' ? 'selected' : ''; ?>>其他</option>
        </select>

        <label for="description">商品描述</label>
        <textarea id="description" name="description" rows="5" required><?php echo htmlspecialchars($product['description']); ?></textarea>

        <label for="attachment">商品圖片</label>
        <?php if (!empty($product['attachment'])): ?>
            <img src="pic/<?php echo htmlspecialchars($product['attachment']); ?>" alt="商品圖片" class="current-image">
        <?php endif; ?>
        <input type="file" id="attachment" name="attachment" accept="image/*">

        <button type="submit">更新商品</button>
    </form>
</body>
</html>