<?php
session_start();
require('../db.inc');

// 檢查是否已登入
if (!isset($_SESSION['user'])) {
    echo "您尚未登入！";
    exit();
}

// 從資料庫中查詢使用者的角色
$userId = $_SESSION['user'];
$sqlRoleCheck = "SELECT Type FROM accounts WHERE Email = ?";
$stmt = mysqli_prepare($link, $sqlRoleCheck);
if (!$stmt) {
    die("SQL 錯誤：" . mysqli_error($link));
}
mysqli_stmt_bind_param($stmt, 's', $userId);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("無法找到使用者，請確認資料庫中是否存在該使用者 ID。");
}

// 檢查使用者角色
if (strtolower($user['Type']) !== 'admin') {
    echo "您無權訪問此頁面！";
    exit();
}

// 查詢所有使用者
$sqlAdmins = "SELECT No, Name, Email, Type FROM accounts WHERE Type = 'Admin'";
$sqlUsers = "SELECT No, Name, Email, Type, Status FROM accounts WHERE Type = 'User'";
$resultAdmins = mysqli_query($link, $sqlAdmins);
$resultUsers = mysqli_query($link, $sqlUsers);
?>

<!DOCTYPE html>
<html lang="zh-TW">
<head>
    <meta charset="UTF-8">
    <title>使用者管理</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
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
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background-color: #f8f9fa;
        }
        .action-button {
            padding: 5px 10px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin: 2px; /* 增加按鈕間距 */
        }
        .promote {
            background-color: #007BFF;
            color: white;
        }
        .demote {
            background-color: #FFC107;
            color: white;
        }
        .block {
            background-color: #DC3545;
            color: white;
        }
        .unblock { /* 新增解封按鈕的通用樣式 */
            background-color: #28a745;
            color: white;
        }
        .action-button:hover {
            opacity: 0.8;
        }
        /* 調整表格樣式，統一列寬和行高 */
        .table-layout {
            table-layout: fixed;
            width: 100%;
            border-collapse: collapse; /* 確保表格邊框不重疊 */
        }

        .table-layout th,
        .table-layout td {
            border: 1px solid #ccc;
            padding: 10px; /* 統一內邊距 */
            text-align: center;
            vertical-align: middle; /* 垂直置中 */
            line-height: 1.5; /* 統一行高 */
            word-wrap: break-word; /* 處理長內容自動換行 */
        }

        /* 一般使用者和被封鎖使用者表格的列寬 */
        .table-layout:not(.admin-table) th:nth-child(1),
        .table-layout:not(.admin-table) td:nth-child(1) {
            width: 10%; /* ID 列寬 */
        }

        .table-layout:not(.admin-table) th:nth-child(2),
        .table-layout:not(.admin-table) td:nth-child(2) {
            width: 25%; /* 名稱列寬 */
        }

        .table-layout:not(.admin-table) th:nth-child(3),
        .table-layout:not(.admin-table) td:nth-child(3) {
            width: 30%; /* 電子郵件列寬 */
        }

        .table-layout:not(.admin-table) th:nth-child(4), /* 狀態或原因 */
        .table-layout:not(.admin-table) td:nth-child(4) {
            width: 20%; /* 狀態或原因列寬 */
        }

        .table-layout:not(.admin-table) th:nth-child(5), /* 操作 */
        .table-layout:not(.admin-table) td:nth-child(5) {
            width: 15%; /* 操作列寬 */
        }

        /* 管理員表格的特定欄寬 */
        .admin-table th:nth-child(1),
        .admin-table td:nth-child(1) {
            width: 10%; /* ID 列寬 */
        }

        .admin-table th:nth-child(2),
        .admin-table td:nth-child(2) {
            width: 35%; /* 名稱列寬 - 調整以分配剩餘空間 */
        }

        .admin-table th:nth-child(3),
        .admin-table td:nth-child(3) {
            width: 40%; /* 電子郵件列寬 - 調整以分配剩餘空間 */
        }

        .admin-table th:nth-child(4), /* 管理員表格的操作欄 */
        .admin-table td:nth-child(4) {
            width: 15%; /* 操作列寬 */
        }

        /* 封鎖原因輸入框的樣式 */
        .block-reason-form textarea {
            width: calc(100% - 10px); /* 考慮 padding 和 border */
            margin-bottom: 5px;
            box-sizing: border-box; /* 讓 padding 和 border 不會增加總寬度 */
        }
        .block-reason-form button {
            width: 100%;
            box-sizing: border-box;
        }

    </style>
    <script>
        // 確認操作的函式
        function confirmAction(action, userName) {
            let message = '';
            if (action === 'promote') {
                message = `是否要將「${userName}」升為管理員？`;
            } else if (action === 'demote') {
                message = `是否要將「${userName}」降為一般使用者？`;
            } else if (action === 'block') {
                // 封鎖操作的確認會在 showBlockReasonForm 中處理或表單提交時處理
                // 此處可以不特別處理，或者如果希望點擊按鈕時就有提示也可以保留
                // message = `是否要封鎖「${userName}」？`;
                return true; // 直接返回 true，讓 showBlockReasonForm 處理
            } else if (action === 'unblock') {
                message = `是否要解封「${userName}」？`;
            }
            if (message) { // 只有當 message 非空時才 confirm
                return confirm(message);
            }
            return true; // 預設返回 true
        }

        // 顯示操作成功訊息
        function showSuccessMessage(message) {
            alert(message);
            // 移除 URL 中的 success 參數，避免重新整理時再次顯示
            if (window.history.replaceState) {
                const cleanUrl = window.location.protocol + "//" + window.location.host + window.location.pathname;
                window.history.replaceState({path: cleanUrl}, '', cleanUrl);
            }
        }

        // 顯示/隱藏封鎖原因輸入框
        function toggleBlockReasonForm(userId) {
            const form = document.getElementById(`blockForm-${userId}`);
            if (form) {
                if (form.style.display === 'none' || form.style.display === '') {
                    form.style.display = 'block';
                } else {
                    form.style.display = 'none';
                }
            }
        }
    </script>
</head>
<body>
    <div class="top-bar">
        <a href="../index.php" class="home-button">返回首頁</a>
        <?php include('../userMenu.php'); // 假設 userMenu.php 存在且功能正常 ?>
    </div>

    <h1>使用者管理</h1>

    <!-- 搜尋框 -->
    <form method="GET" action="userSearchResult.php" style="margin-bottom: 20px;">
        <input type="text" name="query" placeholder="輸入使用者名稱或電子郵件" required style="padding: 10px; width: 300px; border: 1px solid #ccc; border-radius: 5px;">
        <button type="submit" style="padding: 10px 15px; background-color: #007BFF; color: white; border: none; border-radius: 5px; cursor: pointer;">搜尋</button>
    </form>

    <h2>管理員</h2>
    <table class="table-layout admin-table">
        <thead>
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>電子郵件</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php mysqli_data_seek($resultAdmins, 0); // 重置指針以防萬一 ?>
            <?php while ($admin = mysqli_fetch_assoc($resultAdmins)): ?>
                <tr>
                    <td><?php echo htmlspecialchars($admin['No']); ?></td>
                    <td>
                        <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($admin['No']); ?>" target="_blank">
                            <?php echo htmlspecialchars($admin['Name']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($admin['Email']); ?></td>
                    <td>
                        <form method="POST" action="updateUser.php" style="display: inline;" onsubmit="return confirmAction('demote', '<?php echo htmlspecialchars(addslashes($admin['Name'])); ?>')">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($admin['No']); ?>">
                            <input type="hidden" name="action" value="demote">
                            <button type="submit" class="action-button demote">降為一般使用者</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>一般使用者</h2>
    <table class="table-layout">
        <thead>
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>電子郵件</th>
                <th>狀態</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php mysqli_data_seek($resultUsers, 0); // 重置指針 ?>
            <?php while ($user_row = mysqli_fetch_assoc($resultUsers)): ?>
                <?php if ($user_row['Status'] !== 'blocked'): ?>
                <tr>
                    <td><?php echo htmlspecialchars($user_row['No']); ?></td>
                    <td>
                        <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($user_row['No']); ?>" target="_blank">
                            <?php echo htmlspecialchars($user_row['Name']); ?>
                        </a>
                    </td>
                    <td><?php echo htmlspecialchars($user_row['Email']); ?></td>
                    <td>正常</td>
                    <td>
                        <form method="POST" action="updateUser.php" style="display: inline;" onsubmit="return confirmAction('promote', '<?php echo htmlspecialchars(addslashes($user_row['Name'])); ?>')">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_row['No']); ?>">
                            <input type="hidden" name="action" value="promote">
                            <button type="submit" class="action-button promote">升為管理員</button>
                        </form>

                        <button class="action-button block" onclick="toggleBlockReasonForm(<?php echo htmlspecialchars($user_row['No']); ?>)">封鎖使用者</button>

                        <form id="blockForm-<?php echo htmlspecialchars($user_row['No']); ?>" method="POST" action="updateUser.php" style="display: none; margin-top: 10px;" class="block-reason-form" onsubmit="return confirmAction('block', '<?php echo htmlspecialchars(addslashes($user_row['Name'])); ?>')">
                            <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($user_row['No']); ?>">
                            <input type="hidden" name="action" value="block">
                            <textarea name="block_reason" placeholder="輸入封鎖原因 (最多100字)" required maxlength="100"></textarea>
                            <button type="submit" class="action-button block">確認封鎖</button>
                        </form>
                    </td>
                </tr>
                <?php endif; ?>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h2>被封鎖的使用者</h2>
    <table class="table-layout">
        <thead>
            <tr>
                <th>ID</th>
                <th>名稱</th>
                <th>電子郵件</th>
                <th>原因</th>
                <th>操作</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // 查詢被封鎖的使用者，包含封鎖原因
            $sqlBlockedUsers = "SELECT No, Name, Email, block_reason FROM accounts WHERE Status = 'blocked'";
            $resultBlockedUsers = mysqli_query($link, $sqlBlockedUsers);
            if ($resultBlockedUsers) { // 檢查查詢是否成功
                while ($blockedUser = mysqli_fetch_assoc($resultBlockedUsers)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($blockedUser['No']); ?></td>
                        <td>
                            <a href="../profile/publicProfile.php?seller_id=<?php echo htmlspecialchars($blockedUser['No']); ?>" target="_blank">
                                <?php echo htmlspecialchars($blockedUser['Name']); ?>
                            </a>
                        </td>
                        <td><?php echo htmlspecialchars($blockedUser['Email']); ?></td>
                        <td><?php echo htmlspecialchars($blockedUser['block_reason']); ?></td>
                        <td>
                            <form method="POST" action="updateUser.php" style="display: inline;" onsubmit="return confirmAction('unblock', '<?php echo htmlspecialchars(addslashes($blockedUser['Name'])); ?>')">
                                <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($blockedUser['No']); ?>">
                                <input type="hidden" name="action" value="unblock">
                                <button type="submit" class="action-button unblock">解封使用者</button>
                            </form>
                        </td>
                    </tr>
            <?php
                endwhile;
            } else {
                echo "<tr><td colspan='5'>查詢被封鎖使用者時發生錯誤：" . mysqli_error($link) . "</td></tr>";
            }
            ?>
        </tbody>
    </table>

    <?php if (isset($_SESSION['success_message'])): // 改用 session 儲存成功訊息 ?>
        <script>
            showSuccessMessage("<?php echo htmlspecialchars($_SESSION['success_message']); ?>");
            <?php unset($_SESSION['success_message']); // 顯示後清除 ?>
        </script>
    <?php endif; ?>

    <?php if (isset($_GET['success'])): // 保留對舊GET參數的兼容，但建議轉用SESSION ?>
        <script>
            showSuccessMessage("<?php echo htmlspecialchars($_GET['success']); ?>");
        </script>
    <?php endif; ?>
</body>
</html>
<?php
mysqli_close($link); // 關閉資料庫連接
?>