<?php
    require('../db.inc');
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

        if (isset($_GET['error'])) {
            if ($_GET['error'] === 'blocked') {
                $reason = isset($_GET['reason']) ? htmlspecialchars(urldecode($_GET['reason'])) : '無具體原因';
                echo "<p style='color: red;'>登入失敗，已被管理員封鎖。原因：$reason</p>";
            } elseif ($_GET['error'] === 'password') {
                echo "<p style='color: red;'>密碼錯誤，請再試一次。</p>";
            } elseif ($_GET['error'] === 'email') {
                echo "<p style='color: red;'>帳號不存在，請檢查電子郵件。</p>";
            }
        }
        ?>
        <form action="loginCheck.php" method="POST">
            <input type="hidden" name="redirect" value="<?php echo isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '../index.php'; ?>">
            郵件：<input type='text' name='email' required><br>
            密碼：<input type='password' name='password' required><br>
            <input type='submit' value='登入'>沒有帳號嗎?請<a href='register.php'>註冊</a>
        </form>
    </body>
</html>