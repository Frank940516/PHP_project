<?php
require('../db.inc');
mysqli_set_charset($link, 'utf8');

$currentDate = date('Y-m-d');

// 1. 將未到生效日的優惠券設為未生效 (is_active = 2)
$sqlFuture = "UPDATE coupons 
              SET is_active = 2 
              WHERE start_date > ?";
$stmtFuture = mysqli_prepare($link, $sqlFuture);
mysqli_stmt_bind_param($stmtFuture, 's', $currentDate);
mysqli_stmt_execute($stmtFuture);

// 2. 將到期日已過的優惠券設為失效 (is_active = 0)
$sqlExpire = "UPDATE coupons 
              SET is_active = 0 
              WHERE expiration_date < ?";
$stmtExpire = mysqli_prepare($link, $sqlExpire);
mysqli_stmt_bind_param($stmtExpire, 's', $currentDate);
mysqli_stmt_execute($stmtExpire);

// 3. 根據實際交易紀錄判斷是否達到兌換上限
$sql = "SELECT id, redeem_limit FROM coupons WHERE expiration_date >= ? AND start_date <= ?";
$stmt = mysqli_prepare($link, $sql);
mysqli_stmt_bind_param($stmt, 'ss', $currentDate, $currentDate);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

while ($row = mysqli_fetch_assoc($result)) {
    $couponId = $row['id'];
    $redeemLimit = $row['redeem_limit'];

    // 先查出優惠券代碼
    $sqlCode = "SELECT code FROM coupons WHERE id = ?";
    $stmtCode = mysqli_prepare($link, $sqlCode);
    mysqli_stmt_bind_param($stmtCode, 'i', $couponId);
    mysqli_stmt_execute($stmtCode);
    $resultCode = mysqli_stmt_get_result($stmtCode);
    $couponCode = mysqli_fetch_assoc($resultCode)['code'];
    mysqli_stmt_close($stmtCode);

    // 查詢實際已用次數（用 code 比對 orders.coupon_code）
    $sqlUsed = "SELECT COUNT(*) AS used_count FROM orders WHERE coupon_code = ?";
    $stmtUsed = mysqli_prepare($link, $sqlUsed);
    mysqli_stmt_bind_param($stmtUsed, 's', $couponCode);
    mysqli_stmt_execute($stmtUsed);
    $resultUsed = mysqli_stmt_get_result($stmtUsed);
    $used = mysqli_fetch_assoc($resultUsed)['used_count'];
    mysqli_stmt_close($stmtUsed);

    if ($used >= $redeemLimit) {
        // 已達上限，設為失效
        $sqlSetInvalid = "UPDATE coupons SET is_active = 0 WHERE id = ?";
        $stmtSetInvalid = mysqli_prepare($link, $sqlSetInvalid);
        mysqli_stmt_bind_param($stmtSetInvalid, 'i', $couponId);
        mysqli_stmt_execute($stmtSetInvalid);
        mysqli_stmt_close($stmtSetInvalid);
    } else {
        // 尚未達上限，設為有效
        $sqlSetActive = "UPDATE coupons SET is_active = 1 WHERE id = ?";
        $stmtSetActive = mysqli_prepare($link, $sqlSetActive);
        mysqli_stmt_bind_param($stmtSetActive, 'i', $couponId);
        mysqli_stmt_execute($stmtSetActive);
        mysqli_stmt_close($stmtSetActive);
    }
}

mysqli_stmt_close($stmt);
mysqli_stmt_close($stmtFuture);
mysqli_stmt_close($stmtExpire);
?>