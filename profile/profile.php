<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 檢查是否登入
if (!isset($_SESSION["user"])) {
    header("Location: ../login/login.php");
    exit();
}

// 獲取目前登入的使用者資料
$userEmail = $_SESSION["user"];
$sql = "SELECT Name, Email FROM accounts WHERE Email = ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 's', $userEmail);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "使用者資料不存在！";
    exit();
}
?>
<html>
    <head>
        <meta charset="UTF-8">
        <title>個人資料</title>
        <style>
            .container {
                width: 50%;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
                background-color: #f9f9f9;
            }
            .container h1 {
                text-align: center;
            }
            .form-group {
                margin-bottom: 15px;
            }
            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }
            .form-group input {
                width: 100%;
                padding: 8px;
                border: 1px solid #ccc;
                border-radius: 4px;
            }
            .form-group button {
                padding: 10px 20px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 4px;
                cursor: pointer;
            }
            .form-group button:hover {
                background-color: #45a049;
            }
            .top-right-buttons {
                position: absolute;
                top: 10px;
                right: 10px;
            }
            .back-home-button {
                margin-bottom: 20px;
            }
            .back-home-button input[type="button"] {
            background-color: #1976d2;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 4px;
            cursor: pointer;
            }
            .back-home-button input[type="button"]:hover {
            background-color: #1565c0;
            }
        </style>
    </head>
    <body>
        <!-- 返回首頁按鈕 -->
        <div class="back-home-button">
            <input type="button" value="返回首頁" onclick="location.href='../index.php'">
        </div>

        <!-- 右上角功能清單 -->
        <div class="top-right-buttons">
            <?php include('../userMenu.php'); ?>
        </div>

        <!-- 使用者資料表單 -->
        <div class="container">
            <h1>個人資料</h1>
            <form action="updateProfile.php" method="POST">
                <div class="form-group">
                    <label for="name">使用者名稱</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['Name']); ?>" maxlength="20" required>
                </div>
                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['Email']); ?>" readonly>
                </div>
                <div class="form-group">
                    <button type="submit">儲存變更</button>
                </div>
            </form>
        </div>
    </body>
</html>