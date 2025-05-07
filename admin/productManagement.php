<?php
require('../db.inc');
session_start(); // 確保啟用 session
mysqli_set_charset($link, 'utf8');

// 查詢所有未刪除的商品，按時間排序，並加入賣家名稱
$sql = "SELECT p.*, u.Name AS seller_name 
        FROM products p 
        LEFT JOIN accounts u ON p.seller_id = u.No 
        WHERE p.is_deleted = 0 
        ORDER BY p.created_at DESC";
$result = mysqli_query($link, $sql);

if (!$result) {
    die("查詢失敗：" . mysqli_error($link));
}

$currentDate = null;
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>商品管理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
            background-color: #f4f7f6;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid #ddd;
        }
        .home-button {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
        }
        .home-button:hover {
            background-color: #2980b9;
        }
        .date-block {
            margin-bottom: 30px;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
        .date-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 15px;
            border-bottom: 2px solid #3498db;
            padding-bottom: 8px;
            color: #2980b9;
        }
        .product-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 0;
            table-layout: fixed;
        }
        .product-table th, 
        .product-table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
            vertical-align: middle;
            word-wrap: break-word;
        }
        .product-table th {
            background-color: #e9ecef;
            color: #495057;
            font-weight: 600;
        }
        .product-table th:nth-child(3), .product-table td:nth-child(3) {
            width: 30%; /* 將描述欄位的寬度調整到原本「種類」的位置 */
        }
        .action-button {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px;
            font-size: 13px;
            transition: background-color 0.2s ease-in-out, opacity 0.2s ease-in-out;
        }
        .edit {
            background-color: #007BFF;
            color: white;
        }
        .edit:hover {
            background-color: #0056b3;
        }
        .delete {
            background-color: #DC3545;
            color: white;
        }
        .delete:hover {
            background-color: #c82333;
        }
        .no-products {
            text-align: center;
            font-size: 16px;
            color: #777;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>
<body>
    <div class="top-bar">
        <!-- 左上角返回首頁 -->
        <a href="../index.php" class="home-button">返回首頁</a>
        <!-- 右上角使用者功能 -->
        <?php include('../userMenu.php'); ?>
    </div>

    <h1>商品管理</h1>

    <?php if (mysqli_num_rows($result) > 0): ?>
        <?php while ($row = mysqli_fetch_assoc($result)): ?>
            <?php
            $productDate = date('Y-m-d', strtotime($row['created_at']));
            if ($currentDate !== $productDate):
                if ($currentDate !== null):
            ?>
                            </tbody>
                        </table>
                    </div> 
                <?php endif; ?>
                <div class="date-block">
                    <div class="date-title"><?php echo htmlspecialchars($productDate); ?></div>
                    <table class="product-table">
                        <thead>
                            <tr>
                                <th>名稱</th>
                                <th>價格</th>
                                <th>描述</th>
                                <th>賣家名稱</th>
                                <th>庫存</th>
                                <th>情況</th>
                                <th>建立時間</th>
                                <th>操作</th>
                            </tr>
                        </thead>
                        <tbody>
            <?php
                $currentDate = $productDate;
            endif;
            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo htmlspecialchars(number_format($row['price'], 0)); ?></td>
                                <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                                <td>
                                    <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($row['seller_id']); ?>" target="_blank">
                                        <?php echo htmlspecialchars($row['seller_name']); ?>
                                    </a>
                                </td>
                                <td><?php echo htmlspecialchars($row['stock']); ?></td>
                                <td><?php echo htmlspecialchars($row['condition']); ?></td>
                                <td><?php echo htmlspecialchars(date('Y-m-d H:i', strtotime($row['created_at']))); ?></td>
                                <td>
                                    <form method="GET" action="../product/edit.php" style="display: inline;">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <button type="submit" class="action-button edit">編輯</button>
                                    </form>
                                    <form method="POST" action="../product/delete.php" style="display: inline;" onsubmit="return confirm('確定要刪除「<?php echo htmlspecialchars(addslashes($row['name'])); ?>」此商品嗎？')">
                                        <input type="hidden" name="id" value="<?php echo htmlspecialchars($row['id']); ?>">
                                        <button type="submit" class="action-button delete">刪除</button>
                                    </form>
                                </td>
                            </tr>
        <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
    <?php else: ?>
        <p class="no-products">目前沒有正在販售的商品。</p>
    <?php endif; ?>
    <?php mysqli_close($link); ?>
</body>
</html>
