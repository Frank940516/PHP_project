<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();
?>
<html>
    <meta charset = "UTF-8">
    <head>
        <title>二手書交易平台-首頁</title>
        <style>
            .top-right-buttons {  /* login & register button */
                position: absolute;
                top: 10px;
                right: 10px;
            }
            .top-right-buttons input {
                margin: 5px;
            }
        </style>
    </head>
    <body>
        <div class="top-right-buttons">
            <?php
                if(isset($_SESSION["user"])){
                    echo "<span>歡迎" . htmlspecialchars($_SESSION["name"]) . "！</span>";
                    echo "<input type='button' value='登出' onclick=\"location.href='logout.php'\">";
                } else {
                    echo "<input type='button' value='登入' onclick=\"location.href='login.php'\">";
                    echo "<input type='button' value='註冊' onclick=\"location.href='register.php'\">";
                }
            ?>
        </div>
    </body>
</html>