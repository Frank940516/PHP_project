<?php
if (isset($_SESSION["user"])) {
    ?>
    <div class="sidebar-menu" id="sidebarMenu">
        <button class="sidebar-toggle" onclick="toggleSidebar()">☰</button>
        <div class="sidebar-content" id="sidebarContent">
            <div class="sidebar-user">
                <span><?php echo htmlspecialchars($_SESSION["name"]); ?></span>
            </div>
            <ul>
                <li><a href="/profile/profile.php">編輯個人資料</a></li>
                <li><a href="/notification/notification.php">通知</a></li>
                <li><a href="/notification/wishList.php">願望清單</a></li>
                <li><a href="/coupon/couponList.php">優惠券</a></li>
                <li><a href="/cart/cart.php">購物車</a></li>
                <li><a href="/cart/buyHistory.php">購買紀錄</a></li>
                <li><a href="/product/addNewProduct.php">新增商品</a></li>
                <li><a href="/product/showList.php">管理訂單</a></li>
                <li><a href="/product/sellHistory.php">訂單紀錄</a></li>
                <hr>
                <?php if (isset($_SESSION["type"]) && $_SESSION["type"] === "Admin"): ?>                    
                    <li><a href="/admin/userManagement.php">使用者管理</a></li>
                    <li><a href="/admin/productManagement.php">商品管理</a></li>
                    <li><a href="/admin/orderHistory.php">交易紀錄</a></li>
                    <li><a href="/coupon/addCoupon.php">新增優惠券</a></li>
                    <li><a href="/admin/couponHistory.php">優惠券紀錄</a></li>
                    <hr>
                <?php endif; ?>
                <li><a href="/login/logout.php">登出</a></li>
            </ul>
        </div>
    </div>
    <style>
        .sidebar-menu {
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 2000;
            display: flex;
            flex-direction: column;
        }
        .sidebar-toggle {
            background: #1976d2;
            color: #fff;
            border: none;
            border-radius: 0 8px 8px 0;
            padding: 12px 16px;
            font-size: 22px;
            cursor: pointer;
            margin: 10px 0 0 0;
            outline: none;
        }
        .sidebar-content {
            background: #fff;
            border-right: 1px solid #ccc;
            box-shadow: 2px 0 8px rgba(0,0,0,0.08);
            width: 220px;
            min-width: 180px;
            max-width: 260px;
            padding: 20px 0 0 0;
            transition: transform 0.3s;
            transform: translateX(0);
            height: 100vh;
            overflow-y: auto;
        }
        .sidebar-menu.collapsed .sidebar-content {
            transform: translateX(-100%);
        }
        .sidebar-user {
            font-weight: bold;
            font-size: 18px;
            text-align: center;
            margin-bottom: 18px;
        }
        .sidebar-content ul {
            list-style: none;
            padding: 0 0 0 0;
            margin: 0;
        }
        .sidebar-content ul li {
            margin: 0;
        }
        .sidebar-content ul li a {
            display: block;
            text-decoration: none;
            color: #222;
            padding: 12px 24px;
            font-size: 15px;
            transition: background 0.2s;
        }
        .sidebar-content ul li a:hover {
            background: #f0f0f0;
        }
        .sidebar-content hr {
            margin: 10px 0;
            border: 0;
            border-top: 1px solid #ccc;
        }
        @media (max-width: 600px) {
            .sidebar-content {
                width: 80vw;
                min-width: 0;
            }
        }
    </style>
    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebarMenu');
            sidebar.classList.toggle('collapsed');
        }
        // 點擊外部自動收合
        document.addEventListener('click', function (event) {
            const sidebar = document.getElementById('sidebarMenu');
            const sidebarContent = document.getElementById('sidebarContent');
            const toggleBtn = document.querySelector('.sidebar-toggle');
            if (
                sidebar &&
                !sidebarContent.contains(event.target) &&
                !toggleBtn.contains(event.target)
            ) {
                sidebar.classList.add('collapsed');
            }
        });
    </script>
    <?php
    } else {
        // 只在首頁顯示註冊/登入按鈕
        $currentPage = basename($_SERVER['PHP_SELF']);
        if ($currentPage === 'index.php') {
    ?>
        <div class="auth-buttons" style="position: fixed; left: 18px; top: 10px; z-index: 3000;">
            <a href="/login/register.php" class="auth-button">註冊</a>
            <a href="/login/login.php" class="auth-button">登入</a>
        </div>
        <style>
            .auth-buttons {
                display: flex;
                flex-direction: row;
                gap: 16px;
            }
            .auth-button {
                text-decoration: none;
                color: white;
                background-color: #1976d2;
                padding: 12px 18px;
                border-radius: 10px;
                font-size: 15px;
                transition: background-color 0.3s ease;
                box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            }
            .auth-button:hover {
                background-color: #0056b3;
            }
        </style>
    <?php
        }
    }
    ?>