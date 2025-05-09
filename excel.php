<?php
require('db.inc');

// 查詢交易紀錄
$sql = "SELECT o.id AS order_id, o.total_amount, o.created_at, o.payment_method, o.coupon_code, o.coupon_discount, 
        a.Name AS user_name
        FROM orders o
        JOIN accounts a ON o.user_id = a.No
        ORDER BY o.created_at DESC";
$result = $link->query($sql);

// 檢查是否點擊了匯出按鈕
if (isset($_POST['export_excel'])) {
    header("Content-Type: application/vnd.ms-excel");
    header("Content-Disposition: attachment; filename=交易紀錄.xls");
    header("Pragma: no-cache");
    header("Expires: 0");

    // 輸出表格標題
    echo "訂單編號\t總金額\t建立時間\t付款方式\t優惠券代碼\t優惠折扣\t使用者名稱\n";

    // 輸出資料
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            echo "{$row['order_id']}\t{$row['total_amount']}\t{$row['created_at']}\t{$row['payment_method']}\t{$row['coupon_code']}\t{$row['coupon_discount']}\t{$row['user_name']}\n";
        }
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="zh-tw">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>交易紀錄</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        th {
            background-color: #f2f2f2;
            text-align: left;
        }
    </style>
</head>
<body>
    <h1>交易紀錄</h1>
    <!-- 匯出按鈕 -->
    <form method="post">
        <button type="submit" name="export_excel">匯出為 Excel</button>
    </form>
    <table>
        <thead>
            <tr>
                <th>訂單編號</th>
                <th>總金額</th>
                <th>建立時間</th>
                <th>付款方式</th>
                <th>優惠券代碼</th>
                <th>優惠折扣</th>
                <th>使用者名稱</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['order_id']; ?></td>
                        <td><?php echo $row['total_amount']; ?></td>
                        <td><?php echo $row['created_at']; ?></td>
                        <td><?php echo $row['payment_method']; ?></td>
                        <td><?php echo $row['coupon_code']; ?></td>
                        <td><?php echo $row['coupon_discount']; ?></td>
                        <td><?php echo $row['user_name']; ?></td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7">無交易紀錄</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
<?php
$link->close();
?>