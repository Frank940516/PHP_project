<?php
include 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $role = $_POST['role'];

    $stmt = $conn->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $email, $password, $role]);
    header("Location: login.php");
    exit;
}
?>
<!-- 註冊表單 -->
<form method="post">
    姓名：<input type="text" name="name" required><br>
    電子信箱：<input type="email" name="email" required><br>
    密碼：<input type="password" name="password" required><br>
    角色：
    <select name="role">
        <option value="buyer">買家</option>
        <option value="seller">賣家</option>
    </select><br>
    <button type="submit">註冊</button>
</form>
