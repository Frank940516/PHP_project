<?php
if (isset($_SESSION["user"])) {
    ?>
    <div class="user-menu">
        <span class="user-name" onclick="toggleMenu()"><?php echo htmlspecialchars($_SESSION["name"]); ?> ▼</span>
        <div class="menu-dropdown" id="menuDropdown">
            <ul>
                <li><a href="/profile/profile.php">個人資料</a></li>
                <li><a href="/cart/cart.php">購物車</a></li>
                <li><a href="/cart/buyHistory.php">購買紀錄</a></li>
                <li><a href="/product/showList.php">管理訂單</a></li>
                <li><a href="/product/sellHistory.php">訂單紀錄</a></li>
                <li><a href="/login/logout.php">登出</a></li>
            </ul>
        </div>
    </div>
    <style>
        .user-menu {
            position: relative;
            display: inline-block;
        }
        .user-name {
            cursor: pointer;
            font-weight: bold;
        }
        .menu-dropdown {
            display: none;
            position: absolute;
            right: 0;
            background-color: white;
            border: 1px solid #ccc;
            border-radius: 8px; /* 圓角 */
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            z-index: 1000;
            width: 150px; /* 固定寬度 */
            padding: 10px 0; /* 增加上下內邊距 */
        }
        .menu-dropdown ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }
        .menu-dropdown ul li {
            margin: 0;
        }
        .menu-dropdown ul li a {
            display: block; /* 將連結設置為區塊元素 */
            text-decoration: none;
            color: black;
            padding: 10px 15px; /* 增加內邊距 */
            transition: background-color 0.3s ease;
            font-size: 14px; /* 調整字體大小 */
        }
        .menu-dropdown ul li a:hover {
            background-color: #f0f0f0; /* 懸停背景色 */
        }
    </style>
    <script>
        function toggleMenu() {
            const menu = document.getElementById('menuDropdown');
            menu.style.display = menu.style.display === 'block' ? 'none' : 'block';
        }
        document.addEventListener('click', function (event) {
            const menu = document.getElementById('menuDropdown');
            const userName = document.querySelector('.user-name');
            if (menu && !menu.contains(event.target) && !userName.contains(event.target)) {
                menu.style.display = 'none';
            }
        });
    </script>
    <?php
} else {
    ?>
    <div class="auth-buttons">
        <a href="/login/register.php" class="auth-button">註冊</a>
        <a href="/login/login.php" class="auth-button">登入</a>
    </div>
    <style>
        .auth-buttons {
            display: flex;
            gap: 10px;
        }
        .auth-button {
            text-decoration: none;
            color: white;
            background-color: #007BFF;
            padding: 10px 15px;
            border-radius: 5px;
            font-size: 14px;
            transition: background-color 0.3s ease;
        }
        .auth-button:hover {
            background-color: #0056b3;
        }
    </style>
    <?php
}
?>