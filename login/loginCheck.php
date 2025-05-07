<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
?>
<html>
    <meta charset="UTF-8">
    <head>
        <title>二手書交易平台-登入檢查</title>
    </head>
    <body>
        <?php
        $userEmail = $_POST["email"];
        $userPassword = $_POST["password"];
        $redirectUrl = isset($_POST['redirect']) && !empty($_POST['redirect']) ? $_POST['redirect'] : '../index.php'; // 預設跳轉到首頁

        $sql = "SELECT * FROM accounts WHERE Email='$userEmail'";
        $result = mysqli_query($link, $sql);
        if (mysqli_num_rows($result) > 0) {
            $row = mysqli_fetch_assoc($result); // account exists

            // 檢查使用者是否被封鎖
            if ($row["Status"] === "blocked") {
                header("Location: login.php?error=blocked&redirect=" . urlencode($redirectUrl));
                exit();
            }

            // 檢查密碼是否正確
            if ($userPassword == $row["Password"]) {
                session_start();
                $_SESSION["user"] = $row["Email"];
                $_SESSION["name"] = $row["Name"];
                $_SESSION["type"] = $row["Type"];
                
                // 確保 $redirectUrl 是相對路徑或安全的 URL
                if (filter_var($redirectUrl, FILTER_VALIDATE_URL) || strpos($redirectUrl, '/') === 0) {
                    header("Location: $redirectUrl");
                } else {
                    header("Location: ../index.php");
                }
                exit();
            } else { // password incorrect
                header("Location: login.php?error=password&redirect=" . urlencode($redirectUrl));
                exit();
            }
        } else { // account does not exist
            header("Location: login.php?error=email&redirect=" . urlencode($redirectUrl));
            exit();
        }
        ?>
    </body>
</html>