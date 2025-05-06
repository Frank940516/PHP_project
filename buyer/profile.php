<?php
session_start();
include '../config.php';
$id = $_SESSION['user_id'];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $stmt = $conn->prepare("UPDATE users SET name = ? WHERE id = ?");
    $stmt->execute([$name, $id]);
    echo "更新成功";
}

$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();
?>
<form method="post">
    姓名：<input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>"><br>
    <button type="submit">儲存</button>
</form>
