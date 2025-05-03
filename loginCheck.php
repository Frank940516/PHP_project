<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
?>
<html>
    <meta charset = "UTF-8">
    <head>
        <title>二手書交易平台-登入檢查</title>
    </head>
    <body>
        <?php
            $userEmail = $_POST["email"];
            $userPassword = $_POST["password"];
            $sql = "SELECT * FROM accounts WHERE Email='$userEmail'";
            $result = mysqli_query($link, $sql);
            if(mysqli_num_rows($result) > 0){
                $row = mysqli_fetch_assoc($result); // account exists
                if($userPassword == $row["Password"]){ // check password
                    session_start();
                    $_SESSION["user"] = $row["Email"];
                    $_SESSION["name"] = $row["Name"];
                    header("Location: index.php"); // redirect to index page
                    exit();
                } else { // password incorrect
                    //header("Location: login.php?error=password"); // redirect to login page with error
                    exit();
                }
            } else { // account does not exist
                header("Location: login.php?error=email"); // redirect to login page with error
                exit();
            }
        ?>
    </body>
</html>