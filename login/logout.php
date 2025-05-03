<?php
session_start();
session_unset();
session_destroy();
$previousPage = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../index.php';
header("Location: $previousPage"); // Redirect to the previous page or index page if not available
exit();
?>