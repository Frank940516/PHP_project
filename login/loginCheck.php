<?php
    require('../db.inc');
    mysqli_set_charset($link, 'utf8');
?>
<html>
    <meta charset = "UTF-8">
    <head>
        <title>二手書交易平台-登入檢查</title>
    </head>
    <body>
        <form action="loginCheck.php" method="POST">
            <input type="hidden" name="redirect" value="<?php echo isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '../index.php'; ?>">
            郵件：<input type='text' name='email' required><br>
            密碼：<input type='password' name='password' required><br>
            <input type='submit' value='登入'>沒有帳號嗎?請<a href='register.php'>註冊</a>
        </form>
        <?php
            $userEmail = $_POST["email"];
            $userPassword = $_POST["password"];
            $redirectUrl = isset($_POST['redirect']) ? $_POST['redirect'] : '../index.php'; // 預設跳轉到首頁

            $sql = "SELECT * FROM accounts WHERE Email='$userEmail'";
            $result = mysqli_query($link, $sql);
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result); // account exists
                if($userPassword == $row["Password"]){ // check password
                    session_start();
                    $_SESSION["user"] = $row["Email"];
                    $_SESSION["name"] = $row["Name"];
                    $_SESSION["type"] = $row["Type"];
                    
                    // 導回使用者原本的頁面
                    header("Location: $redirectUrl");
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