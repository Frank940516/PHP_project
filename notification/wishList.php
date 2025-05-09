<?php
require('../db.inc');
session_start();
mysqli_set_charset($link, 'utf8');

// 檢查登入
if (!isset($_SESSION['user'])) {
    header("Location: ../login/login.php");
    exit();
}

// 取得登入者資訊
$userEmail = $_SESSION['user'];
$sqlUser = "SELECT No, Name FROM accounts WHERE Email = ?";
$stmtUser = mysqli_prepare($link, $sqlUser);
mysqli_stmt_bind_param($stmtUser, 's', $userEmail);
mysqli_stmt_execute($stmtUser);
$resultUser = mysqli_stmt_get_result($stmtUser);
$user = mysqli_fetch_assoc($resultUser);
$userId = $user['No'];

// 新增願望清單
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add') {
    $bookName = trim($_POST['book_name']);
    $author = trim($_POST['author']);
    if ($bookName !== '') {
        $sqlAdd = "INSERT INTO wish_list (user_id, book_name, author) VALUES (?, ?, ?)";
        $stmtAdd = mysqli_prepare($link, $sqlAdd);
        mysqli_stmt_bind_param($stmtAdd, 'iss', $userId, $bookName, $author);
        mysqli_stmt_execute($stmtAdd);
    }
    header("Location: wishList.php");
    exit();
}

// 更新願望清單
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update') {
    $wishId = $_POST['wish_id'];
    $field = $_POST['field']; // 接收欄位名稱
    $value = trim($_POST['value']); // 接收欄位值
    $maxLength = 50; // 設定字數上限

    // 確保欄位名稱合法且值不為空且不超過字數限制
    if (in_array($field, ['book_name', 'author']) && $value !== '' && mb_strlen($value) <= $maxLength) {
        $sqlUpdate = "UPDATE wish_list SET {$field} = ? WHERE id = ? AND user_id = ?";
        $stmtUpdate = mysqli_prepare($link, $sqlUpdate);
        mysqli_stmt_bind_param($stmtUpdate, 'sii', $value, $wishId, $userId);
        mysqli_stmt_execute($stmtUpdate);
    } else {
        http_response_code(400); // 回傳 400 錯誤
        if ($value === '') {
            echo "欄位值不能為空！";
        } elseif (mb_strlen($value) > $maxLength) {
            echo "欄位值不能超過 {$maxLength} 個字！";
        }
        exit();
    }
    exit(); // 返回 AJAX 請求
}

// 刪除願望清單
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete') {
    $wishId = $_POST['wish_id'];
    $sqlDelete = "DELETE FROM wish_list WHERE id = ? AND user_id = ?";
    $stmtDelete = mysqli_prepare($link, $sqlDelete);
    mysqli_stmt_bind_param($stmtDelete, 'ii', $wishId, $userId);
    mysqli_stmt_execute($stmtDelete);
    header("Location: wishList.php");
    exit();
}

// 取得願望清單
$sqlWish = "SELECT * FROM wish_list WHERE user_id = ? ORDER BY created_at DESC";
$stmtWish = mysqli_prepare($link, $sqlWish);
mysqli_stmt_bind_param($stmtWish, 'i', $userId);
mysqli_stmt_execute($stmtWish);
$resultWish = mysqli_stmt_get_result($stmtWish);
?>
<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>願望清單</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
        .top-bar {
            display: flex; justify-content: space-between; align-items: center;
            padding: 10px 20px; background-color: #f8f9fa; border-bottom: 1px solid #ddd;
        }
        .announcement-button {
            background-color: #007BFF; color: white; padding: 10px 15px; border: none;
            border-radius: 5px; font-size: 14px; cursor: pointer; text-decoration: none;
        }
        .announcement-button:hover { background-color: #0056b3; }
        .top-right-buttons { display: flex; gap: 10px; }
        .container { max-width: 600px; margin: 30px auto; background: #fff; border-radius: 8px; box-shadow: 0 0 8px #eee; padding: 30px; }
        h2 { margin-bottom: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 8px; border-bottom: 1px solid #eee; }
        th { background: #f2f2f2; }
        input[type="text"] { width: 90%; padding: 5px; }
        .action-buttons button { padding: 5px 10px; }
    </style>
    <script>
        function updateWish(wishId, field, value, originalValue, inputElement) {
            const errorElement = inputElement.nextElementSibling;
            const maxLength = 50; // 設定字數上限

            if (value.trim() === '') {
                // 回退到原本的值
                inputElement.value = originalValue;

                // 顯示紅字提示
                if (errorElement) {
                    errorElement.textContent = '欄位值不能為空！';
                }
                return;
            }

            if (value.length > maxLength) {
                // 回退到原本的值
                inputElement.value = originalValue;

                // 顯示紅字提示
                if (errorElement) {
                    errorElement.textContent = `欄位值不能超過 ${maxLength} 個字！`;
                }
                return;
            }

            // 清除紅字提示
            if (errorElement) {
                errorElement.textContent = '';
            }

            const formData = new FormData();
            formData.append('action', 'update');
            formData.append('wish_id', wishId);
            formData.append('field', field); // 傳遞欄位名稱
            formData.append('value', value); // 傳遞欄位值

            fetch('wishList.php', {
                method: 'POST',
                body: formData
            }).then(response => {
                if (!response.ok) {
                    response.text().then(text => {
                        // 顯示後端錯誤訊息
                        if (errorElement) {
                            errorElement.textContent = text;
                        }
                        // 回退到原本的值
                        inputElement.value = originalValue;
                    });
                }
            });
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <a href="../index.php" class="announcement-button">回首頁</a>
        <div class="top-right-buttons">
            <?php include('../userMenu.php'); ?>
        </div>
    </div>
    <div class="container">
        <h2>我的願望清單</h2>
        <form method="post">
            <input type="hidden" name="action" value="add">
            <input type="text" name="book_name" placeholder="書名" required>
            <input type="text" name="author" placeholder="作者">
            <button type="submit">新增</button>
        </form>
        <table>
            <thead>
                <tr>
                    <th>書名</th>
                    <th>作者</th>
                    <th>加入時間</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_assoc($resultWish)): ?>
                    <tr>
                        <td>
                            <input type="text" value="<?php echo htmlspecialchars($row['book_name']); ?>" 
                                   onblur="updateWish(<?php echo $row['id']; ?>, 'book_name', this.value, '<?php echo htmlspecialchars($row['book_name']); ?>', this)">
                            <span style="color: red; font-size: 12px; height: 16px; display: block;"></span>
                        </td>
                        <td>
                            <input type="text" value="<?php echo htmlspecialchars($row['author']); ?>" 
                                   onblur="updateWish(<?php echo $row['id']; ?>, 'author', this.value, '<?php echo htmlspecialchars($row['author']); ?>', this)">
                            <span style="color: red; font-size: 12px; height: 16px; display: block;"></span>
                        </td>
                        <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                        <td>
                            <form method="post" style="display:inline;">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="wish_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" onclick="return confirm('確定要刪除嗎？')">刪除</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>