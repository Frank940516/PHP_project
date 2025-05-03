<?php
    require('db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();
?>
<html>
    <meta charset = "UTF-8">
    <head>
        <title>二手書交易平台-註冊檢查</title>
    </head>
    <body>
        <?php
            $email = $_POST["email"];
            $password = $_POST["password"];
            $name = $_POST["name"];

            $sql = "SELECT * FROM accounts WHERE Email='$email'"; //check duplicate email
            $result = mysqli_query($link, $sql);
            if(mysqli_num_rows($result) > 0){
                header("Location: register.php?error=exists"); // exists email
                exit();
            } else { // add new account to database
                $type = 'User';
                $insetsSql = "INSERT INTO accounts (Email, Password, Name, Type) VALUES ('$email', '$password', '$name', '$type')";
                if (mysqli_query($link, $insetsSql)) {
                    $_SESSION["user"] = $email;
                    $_SESSION["name"] = $name;
                    header("Location: index.php"); // redirect to index page
                    exit();
                } else {
                    echo "Error: " . $insetsSql . "<br>" . mysqli_error($link);
                }
            }
        ?>
    </body>
</html>