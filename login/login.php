<?php
    require('../db.inc');
    mysqli_set_charset($link, 'utf8');
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>二手書交易平台-登入</title>
    <style>
        body {
            background: #f7f8fa;
            font-family: 'Microsoft JhengHei', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .login-container {
            width: 350px;
            margin: 80px auto;
            background: #fff;
            border-radius: 18px;
            box-shadow: 0 4px 24px rgba(0,0,0,0.08);
            padding: 40px 32px 32px 32px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .login-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 24px;
            color: #222;
            letter-spacing: 2px;
        }
        .login-form {
            width: 100%;
        }
        .login-form label {
            display: block;
            margin-bottom: 6px;
            color: #444;
            font-size: 1rem;
        }
        .login-form input[type="text"],
        .login-form input[type="password"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #d0d0d0;
            border-radius: 8px;
            font-size: 1rem;
            background: #fafbfc;
            transition: border 0.2s;
        }
        .login-form input[type="text"]:focus,
        .login-form input[type="password"]:focus {
            border: 1.5px solid #1976d2;
            outline: none;
            background: #fff;
        }
        .login-form .login-btn {
            width: 100%;
            padding: 12px 0;
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            transition: background 0.2s;
            margin-bottom: 10px;
        }
        .login-form .login-btn:hover {
            background: #1565c0;
        }
        .register-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #1976d2;
            text-decoration: none;
            font-size: 1rem;
        }
        .register-link:hover {
            text-decoration: underline;
        }
        .error-message {
            color: #e53935;
            background: #fff3f3;
            border: 1px solid #ffcdd2;
            border-radius: 6px;
            padding: 8px 12px;
            margin-bottom: 18px;
            width: 100%;
            text-align: center;
            font-size: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-title">登入</div>
        <?php
        if (isset($_GET["error"])) {
            if ($_GET["error"] == "password") {
                echo "<div class='error-message'>密碼錯誤！</div>";
            } elseif ($_GET["error"] == "email") {
                echo "<div class='error-message'>帳號不存在！</div>";
            }
        }
        ?>
        <form class="login-form" action="loginCheck.php" method="POST">
            <input type="hidden" name="redirect" value="<?php echo isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '../index.php'; ?>">
            <label for="email">郵件</label>
            <input type="text" id="email" name="email" required>
            <label for="password">密碼</label>
            <input type="password" id="password" name="password" required>
            <input class="login-btn" type="submit" value="登入">
        </form>
        <a class="register-link" href="register.php">沒有帳號嗎？請註冊</a>
    </div>
</body>
</html>