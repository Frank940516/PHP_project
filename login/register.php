<?php
    require('../db.inc');
    mysqli_set_charset($link, 'utf8');
    session_start();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
?>
<!DOCTYPE html>
<html lang="zh-Hant">
<head>
    <meta charset="UTF-8">
    <title>二手書交易平台-註冊</title>
    <style>
        body {
            background: #f7f8fa;
            font-family: 'Microsoft JhengHei', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        .register-container {
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
        .register-title {
            font-size: 2rem;
            font-weight: bold;
            margin-bottom: 24px;
            color: #222;
            letter-spacing: 2px;
        }
        .register-form {
            width: 100%;
            position: relative;
        }
        .register-form label {
            display: block;
            margin-bottom: 6px;
            color: #444;
            font-size: 1rem;
        }
        .register-form input[type="text"],
        .register-form input[type="password"],
        .register-form input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #d0d0d0;
            border-radius: 8px;
            font-size: 1rem;
            background: #fafbfc;
            transition: border 0.2s;
        }
        .register-form input[type="text"]:focus,
        .register-form input[type="password"]:focus,
        .register-form input[type="email"]:focus {
            border: 1.5px solid #1976d2;
            outline: none;
            background: #fff;
        }
        .register-form .register-btn {
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
        .register-form .register-btn:hover {
            background: #1565c0;
        }
        .login-link {
            display: block;
            text-align: center;
            margin-top: 10px;
            color: #1976d2;
            text-decoration: none;
            font-size: 1rem;
        }
        .login-link:hover {
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
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #1976d2;
        }
        .toggle-password:hover {
            color: #1565c0;
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-title">註冊</div>
        <?php
        if (isset($_GET["error"])) {
            if ($_GET["error"] == "email") {
                echo "<div class='error-message'>此郵件已被註冊！</div>";
            } elseif ($_GET["error"] == "password") {
                echo "<div class='error-message'>密碼不一致！</div>";
            }
        }
        ?>
        <form class="register-form" action="registerCheck.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <label for="email">郵件</label>
            <input type="email" id="email" name="email" maxlength="255" required>
            <label for="username">用戶名稱</label>
            <input type="text" id="username" name="username" maxlength="50" required>
            <label for="password">密碼</label>
            <div style="position: relative;">
                <input type="password" id="password" name="password" maxlength="128" required>
                <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁️</button>
            </div>
            <label for="confirm_password">確認密碼</label>
            <div style="position: relative;">
                <input type="password" id="confirm_password" name="confirm_password" maxlength="128" required>
                <button type="button" class="toggle-password" onclick="togglePassword('confirm_password', this)">👁️</button>
            </div>
            <input class="register-btn" type="submit" value="註冊">
        </form>
        <a class="login-link" href="login.php">已經有帳號？登入</a>
    </div>
    <script>
        function togglePassword(fieldId, toggleElement) {
            const passwordField = document.getElementById(fieldId);
            if (passwordField.type === 'password') {
                passwordField.type = 'text';
                toggleElement.textContent = '👁️‍🗨️';
            } else {
                passwordField.type = 'password';
                toggleElement.textContent = '👁️';
            }
        }
    </script>
</body>
</html>