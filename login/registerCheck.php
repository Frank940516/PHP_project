<?php
    require('../db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();

    // 驗證 CSRF Token
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
        die('CSRF 驗證失敗');
    }

    // 接收輸入
    $email = trim($_POST["email"]);
    $password = $_POST["password"];
    $confirmPassword = $_POST["confirm_password"];
    $name = trim($_POST["username"]);

    // 驗證輸入長度
    if (strlen($email) > 255 || strlen($password) > 128 || strlen($name) > 50) {
        header("Location: register.php?error=invalid_input");
        exit();
    }

    // 驗證密碼一致性
    if ($password !== $confirmPassword) {
        header("Location: register.php?error=password");
        exit();
    }

    // 檢查是否已存在相同的 Email
    $sql = "SELECT * FROM accounts WHERE Email = ?";
    $stmt = mysqli_prepare($link, $sql);
    mysqli_stmt_bind_param($stmt, 's', $email);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if (mysqli_num_rows($result) > 0) {
        // Email 已存在
        header("Location: register.php?error=email");
        exit();
    } else {
        // 加密密碼
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // 新增帳號到資料庫
        $type = 'User';
        $insertSql = "INSERT INTO accounts (Email, Password, Name, Type) VALUES (?, ?, ?, ?)";
        $stmtInsert = mysqli_prepare($link, $insertSql);
        mysqli_stmt_bind_param($stmtInsert, 'ssss', $email, $hashedPassword, $name, $type);

        if (mysqli_stmt_execute($stmtInsert)) {
            // 註冊成功，設定 Session
            $_SESSION["user"] = $email;
            $_SESSION["name"] = $name;
            $_SESSION["type"] = 'User'; // 預設為普通使用者
            header("Location: ../index.php");
            exit();
        } else {
            echo "Error: " . mysqli_error($link);
        }
    }
?>
<html>
    <meta charset = "UTF-8">
    <head>
        <title>二手書交易平台-註冊檢查</title>
    </head>
    <body>
    </body>
</html>