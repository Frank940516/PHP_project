<?php
require('../db.inc'); // 資料庫連線檔案
require('../authCheck.php'); 
mysqli_set_charset($link, 'utf8');

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

// 查詢使用者上架的商品
$sqlProducts = "SELECT id, name, price, stock, `condition`, description, attachment 
                FROM products 
                WHERE seller_id = ? AND is_deleted = 0"; // 新增條件 is_deleted = 0
$stmtProducts = mysqli_prepare($link, $sqlProducts);
mysqli_stmt_bind_param($stmtProducts, 'i', $userId);
mysqli_stmt_execute($stmtProducts);
$resultProducts = mysqli_stmt_get_result($stmtProducts);

$products = [];
while ($row = mysqli_fetch_assoc($resultProducts)) {
    $products[] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>我的商品列表</title>
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
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        .add-product-button {
            padding: 8px 15px;
            background-color: #28a745;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            font-size: 14px;
        }
        .add-product-button:hover {
            background-color: #218838;
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
        th {
            background-color: #f2f2f2;
        }
        .action-buttons a {
            margin-right: 10px;
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 5px 10px;
            border-radius: 5px;
        }
        .action-buttons a:hover {
            background-color: #0056b3;
        }
        .delete-button {
            background-color: #dc3545;
        }
        .delete-button:hover {
            background-color: #c82333;
        }
        .product-image {
            max-width: 100px;
            max-height: 100px;
        }
    </style>
</head>
<body>
    <!-- Top bar with back home button and user menu -->
    <div class="top-bar">
        <div class="back-home-button">
            <input type="button" value="返回首頁" onclick="location.href='../index.php'">
        </div>
        <?php require('../userMenu.php'); ?>
    </div>

    <!-- Header with title and add product button -->
    <div class="header-container">
        <h1>我的商品列表</h1>
        <a href="addNewProduct.php" class="add-product-button">新增商品</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>圖片</th>
                <th>商品名稱</th>
                <th>價格</th>
                <th>庫存</th>
                <th>狀況</th>
                <th>描述</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($products)): ?>
                <?php foreach ($products as $product): ?>
                    <tr>
                        <td>
                            <?php if (!empty($product['attachment'])): ?>
                                <img src="pic/<?php echo htmlspecialchars($product['attachment']); ?>" alt="商品圖片" class="product-image">
                            <?php else: ?>
                                無圖片
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($product['name']); ?></td>
                        <td><?php echo htmlspecialchars($product['price']); ?></td>
                        <td><?php echo htmlspecialchars($product['stock']); ?></td>
                        <td><?php echo htmlspecialchars($product['condition']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($product['description'])); ?></td>
                        <td class="action-buttons">
                            <a href="edit.php?id=<?php echo $product['id']; ?>">編輯</a>
                            <a href="delete.php?id=<?php echo $product['id']; ?>" class="delete-button" onclick="return confirm('確定要刪除此商品嗎？');">刪除</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">目前沒有上架的商品。</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>