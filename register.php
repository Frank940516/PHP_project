<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();
?>
<html>
    <meta charset = "UTF-8">
    <head>
        <title>二手書交易平台-註冊</title>
    </head>
    <body>
        <?php
            echo "請在此註冊：<br>";

            //show error message if exists
            if (isset($_GET["error"])) {
                echo "<p style='color: red;'>該帳號已註冊過！</p>";
            }

            echo "<form action='registerCheck.php' method='post'>";
            echo "郵件：<input type='text' name='email' required><br>";
            echo "密碼：<input type='password' name='password' required><br>";
            echo "使用者名稱：<input type='text' name='name' required><br>";
            echo "<input type='submit' value='註冊'>"."已有帳號嗎?請<a href='login.php'>登入</a>";
        ?>
    </body>
</html>