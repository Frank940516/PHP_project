<?php
session_start();
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $role = $_POST['role']; // buyer / seller / admin

    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND role = ?");
    $stmt->execute([$email, $role]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: dashboard.php");
        exit;
    } else {
        $error = "帳號或密碼錯誤";
    }
}
?>
<!-- 登入表單 -->
<form method="post">
    電子信箱：<input type="email" name="email" required><br>
    密碼：<input type="password" name="password" required><br>
    角色：
    <select name="role">
        <option value="buyer">買家</option>
        <option value="seller">賣家</option>
        <option value="admin">管理員</option>
    </select><br>
    <button type="submit">登入</button>
</form>
<?php if (!empty($error)) echo "<p>$error</p>"; ?>
