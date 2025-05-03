<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
?>
<html>
    <meta charset = "UTF-8">
    <head>
        <title>二手書交易平台-登入</title>
    </head>
    <body>
        <?php
        echo "已有帳號？請在此登入：<br>";

        if (isset($_GET["error"])) {
            if ($_GET["error"] == "password") {
                echo "<p style='color: red;'>密碼錯誤！</p>";
            } elseif ($_GET["error"] == "email") {
                echo "<p style='color: red;'>帳號不存在！</p>";
            }
        }
        echo "<form action='loginCheck.php' method='post'>";
        echo "郵件：<input type='text' name='email' required><br>";
        echo "密碼：<input type='password' name='password' required><br>";
        echo "<input type='submit' value='登入'>"."沒有帳號嗎?請<a href='register.php'>註冊</a>";
        ?>
    </body> 