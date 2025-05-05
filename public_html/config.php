<?php
$host = "localhost"; // 或 InfinityFree 的 DB host
$dbname = "your_db_name";
$username = "your_username";
$password = "your_password";

try {
    $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo "資料庫連線失敗：" . $e->getMessage();
    exit;
}
?>
