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

// 3. 將已達到兌換次數限制的優惠券設為失效 (is_active = 0)
$sqlRedeemLimit = "UPDATE coupons 
                   SET is_active = 0 
                   WHERE redeem_count >= redeem_limit";
$stmtRedeemLimit = mysqli_prepare($link, $sqlRedeemLimit);
mysqli_stmt_execute($stmtRedeemLimit);

// 4. 將生效中的優惠券設為有效 (is_active = 1)
$sqlActive = "UPDATE coupons 
              SET is_active = 1 
              WHERE start_date <= ? AND expiration_date >= ? AND redeem_count < redeem_limit";
$stmtActive = mysqli_prepare($link, $sqlActive);
mysqli_stmt_bind_param($stmtActive, 'ss', $currentDate, $currentDate);
mysqli_stmt_execute($stmtActive);

// 關閉語句
mysqli_stmt_close($stmtFuture);
mysqli_stmt_close($stmtExpire);
mysqli_stmt_close($stmtRedeemLimit);
mysqli_stmt_close($stmtActive);
?>