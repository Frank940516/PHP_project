<?php
        require('../db.inc');
        mysqli_set_charset($link, 'utf8');
        session_start();

        $announcementNo = isset($_GET['No']) ? intval($_GET['No']) : 0;

        // Show the announcement details
        $sql = "SELECT announcement.Title,announcement.Content,
                FROM announcement
                JOIN accounts ON announcement.Publisher = accounts.No
                WHERE announcement.No = ?";
        $stmt = mysqli_prepare($link, $sql);
        mysqli_stmt_bind_param($stmt, 'i', $announcementNo);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $announcement = mysqli_fetch_assoc($result);

        if(!$announcement) { // No announcement found
            echo "<h1>公告不存在</h1>";
            header("Location: announcement.php");
            exit();
        }

        $pageTitle = htmlspecialchars($announcement['Title']);
?>
<html>
    <head>
        <meta charset = "UTF-8">
        <title><?php echo $pageTitle; ?> - 公告細節</title>
    </head>
    <body>
        <h1><?php echo $pageTitle; ?></h1>
        <p><strong>發布者：</strong><?php echo htmlspecialchars($announcement['PublisherName']); ?></p>
        <p><strong>公告日期：</strong><?php echo htmlspecialchars($announcement['Date']); ?></p>
        <p><?php echo nl2br(htmlspecialchars($announcement['Content'])); ?></p>
    </body>
</html>