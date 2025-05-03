<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');
session_start();

// 檢查是否為管理員
if (!isset($_SESSION["type"]) || $_SESSION["type"] !== "Admin") {
    header("Location: ../index.php");
    exit();
}
?>

<html>
    <head>
        <meta charset="UTF-8">
        <title>新增公告</title>
        <style>
            .form-container {
                width: 50%;
                margin: 0 auto;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 5px;
                background-color: #f9f9f9;
            }
            .form-container h1 {
                text-align: center;
            }
            .form-container label {
                display: block;
                margin-top: 10px;
                font-weight: bold;
            }
            .form-container textarea {
                width: 100%;
                height: 150px;
                margin-top: 5px;
                padding: 10px;
                font-size: 14px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
            .form-container input[type="text"] {
                width: 100%;
                padding: 10px;
                margin-top: 5px;
                font-size: 14px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }
            .form-container .markdown-tools {
                margin-top: 10px;
            }
            .form-container .markdown-tools button {
                margin-right: 5px;
                padding: 5px 10px;
                font-size: 14px;
                cursor: pointer;
            }
            .form-container input[type="submit"] {
                margin-top: 20px;
                padding: 10px 20px;
                font-size: 16px;
                background-color: #4CAF50;
                color: white;
                border: none;
                border-radius: 5px;
                cursor: pointer;
            }
            .form-container input[type="submit"]:hover {
                background-color: #45a049;
            }
        </style>
        <script>
            function insertMarkdown(tag) {
                const textarea = document.getElementById("content");
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                const text = textarea.value;
                const selectedText = text.substring(start, end);
                const markdown = tag + selectedText + tag;
                textarea.value = text.substring(0, start) + markdown + text.substring(end);
                textarea.focus();
                textarea.setSelectionRange(start + tag.length, end + tag.length);
            }
        </script>
    </head>
    <body>
        <div class="form-container">
            <h1>新增公告</h1>
            <form action="saveAnnouncement.php" method="POST">
                <label for="title">公告標題</label>
                <input type="text" id="title" name="title" required>

                <label for="content">公告內容</label>
                <div class="markdown-tools">
                    <button type="button" onclick="insertMarkdown('**')">粗體</button>
                    <button type="button" onclick="insertMarkdown('_')">斜體</button>
                </div>
                <textarea id="content" name="content" required></textarea>

                <input type="submit" value="發布公告">
            </form>
        </div>
    </body>
</html>