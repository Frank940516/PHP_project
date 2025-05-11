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
        .login-form input[type="email"] {
            width: 100%;
            padding: 10px 12px;
            margin-bottom: 18px;
            border: 1px solid #d0d0d0;
            border-radius: 8px;
            font-size: 1rem;
            background: #fafbfc;
            transition: border 0.2s;
            box-sizing: border-box;
        }
        .login-form input[type="email"]:focus {
            border: 1.5px solid #1976d2;
            outline: none;
            background: #fff;
        }
        .password-container {
            display: flex;
            align-items: center;
            margin-bottom: 18px;
            position: relative;
        }
        .password-container input[type="password"],
        .password-container input[type="text"] {
            flex: 1;
            width: 100%;
            padding: 10px 12px;
            border: 1px solid #d0d0d0;
            border-radius: 8px;
            font-size: 1rem;
            background: #fafbfc;
            transition: border 0.2s;
            box-sizing: border-box;
            padding-right: 38px;
        }
        .password-container input[type="password"]:focus,
        .password-container input[type="text"]:focus {
            border: 1.5px solid #1976d2;
            outline: none;
            background: #fff;
        }
        .toggle-password {
            margin-left: -36px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 1.2rem;
            color: #1976d2;
            z-index: 2;
        }
        .toggle-password:hover {
            color: #1565c0;
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
            color: #fff;
            background: #e53935;
            padding: 10px 0;
            border-radius: 6px;
            margin-bottom: 18px;
            text-align: center;
            font-weight: bold;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-title">登入</div>
        <?php
        if (isset($_GET['error'])) {
            if ($_GET['error'] === 'blocked') {
                $reason = isset($_GET['reason']) ? htmlspecialchars(urldecode($_GET['reason'])) : '無具體原因';
                echo "<div class='error-message'>登入失敗，已被管理員封鎖。原因：$reason</div>";
            } elseif ($_GET['error'] === 'password') {
                echo "<div class='error-message'>密碼錯誤，請再試一次。</div>";
            } elseif ($_GET['error'] === 'email') {
                echo "<div class='error-message'>帳號不存在，請檢查電子郵件。</div>";
            } elseif ($_GET['error'] === 'invalid_credentials') {
                echo "<div class='error-message'>帳號或密碼錯誤，請再試一次。</div>";
            } else {
                echo "<div class='error-message'>登入失敗，請再試一次。</div>";
            }
        }
        ?>
        <form class="login-form" action="loginCheck.php" method="POST">
            <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
            <input type="hidden" name="redirect" value="<?php echo isset($_GET['redirect']) ? htmlspecialchars($_GET['redirect']) : '../index.php'; ?>">
            <label for="email">郵件</label>
            <input type="email" id="email" name="email" required>
            <label for="password">密碼</label>
            <div class="password-container">
                <input type="password" id="password" name="password" required>
                <button type="button" class="toggle-password" onclick="togglePassword('password', this)">👁️</button>
            </div>
            <input class="login-btn" type="submit" value="登入">
        </form>
        <a class="register-link" href="register.php">還沒有帳號？註冊</a>
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