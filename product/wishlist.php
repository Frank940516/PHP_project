<?php
// filepath: c:\xampp\htdocs\php_project\product\wantlist.php
require('../db.inc');
session_start();
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

// 查詢願望清單商品
$sql = "SELECT w.id AS wish_id, p.id, p.name, p.price, p.attachment, p.category
        FROM wishlist w
        JOIN products p ON w.product_id = p.id
        WHERE w.user_id = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'i', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$wishlist = [];
while ($row = mysqli_fetch_assoc($result)) {
    $wishlist[] = $row;
}
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>願望清單</title>
    <style>
        body { font-family: 'Microsoft JhengHei', Arial, sans-serif; background: #f7f8fa; margin: 0; }
        .container { max-width: 800px; margin: 40px auto; background: #fff; border-radius: 12px; box-shadow: 0 4px 16px rgba(0,0,0,0.08); padding: 32px; }
        h1 { text-align: center; margin-bottom: 32px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; border-bottom: 1px solid #eee; text-align: center; }
        th { background: #f2f2f2; }
        img { max-width: 80px; max-height: 80px; border-radius: 6px; }
        .remove-btn {
            background: #e74c3c; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; cursor: pointer;
        }
        .remove-btn:hover { background: #c0392b; }
    </style>
</head>
<body>
    <div class="container">
        <h1>願望清單</h1>
        <?php if (empty($wishlist)): ?>
            <p style="text-align:center;">您的願望清單是空的。</p>
        <?php else: ?>
            <table>
                <thead>
                    <tr>
                        <th>圖片</th>
                        <th>商品名稱</th>
                        <th>價格</th>
                        <th>分類</th>
                        <th>操作</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($wishlist as $item): ?>
                    <tr>
                        <td>
                            <?php if (!empty($item['attachment'])): ?>
                                <img src="pic/<?php echo htmlspecialchars($item['attachment']); ?>" alt="商品圖片">
                            <?php else: ?>
                                無圖片
                            <?php endif; ?>
                        </td>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td><?php echo htmlspecialchars($item['price']); ?></td>
                        <td><?php echo htmlspecialchars($item['category']); ?></td>
                        <td>
                            <form method="post" action="wantlist.php" style="display:inline;">
                                <input type="hidden" name="remove_id" value="<?php echo $item['wish_id']; ?>">
                                <button type="submit" class="remove-btn" onclick="return confirm('確定要移除嗎？');">移除</button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        <?php endif; ?>
        <div style="text-align:center; margin-top:24px;">
            <a href="../index.php">返回首頁</a>
        </div>
    </div>
<?php
// 移除願望清單項目
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['remove_id'])) {
    $removeId = intval($_POST['remove_id']);
    $sqlDel = "DELETE FROM wishlist WHERE id = ? AND user_id = ?";
    $stmtDel = mysqli_prepare($link, $sqlDel);
    mysqli_stmt_bind_param($stmtDel, 'ii', $removeId, $userId);
    mysqli_stmt_execute($stmtDel);
    // 重新導向避免重複提交
    header("Location: wantlist.php");
    exit();
}
?>
</body>
</html>