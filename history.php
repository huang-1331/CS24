<?php
session_start();
include "db.php";
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>주문 및 픽업 내역</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; text-align: center; margin: 0; }
        header { background: #1e3a8a; color: white; padding: 20px; }
        .container { max-width: 900px; margin: 30px auto; background: white; padding: 20px; border-radius: 10px; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; }
        th { background: #f0f4ff; color: #1e3a8a; }
        .status-ready { color: #f59e0b; font-weight: bold; }
        .status-done { color: green; font-weight: bold; }
        .pickup-code { font-family: monospace; font-size: 18px; font-weight: bold; letter-spacing: 2px; background: #eee; padding: 3px 8px; border-radius: 4px; }
    </style>
</head>
<body>
    <header>
        <h2>📦 내 주문 / 픽업 내역</h2>
    </header>
    <div class="container">
        <table>
            <thead>
                <tr>
                    <th>주문일시</th>
                    <th>결제금액</th>
                    <th>결제수단</th>
                    <th>픽업 매장</th>
                    <th>진행 상태</th>
                    <th>픽업 바코드(인증번호)</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // 사용자의 주문 내역 조회
                $sql = "SELECT o.createdAt, o.orderTotalAmount, o.orderPaymentMethod, o.orderStatus, o.orderPickupCode, s.storeName 
                        FROM P_ORDER o 
                        JOIN P_STORE s ON o.storeId = s.storeId 
                        WHERE o.userId = '$user_id' 
                        ORDER BY o.createdAt DESC";
                $result = mysqli_query($conn, $sql);

                if(mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['createdAt'] . "</td>";
                        echo "<td>" . number_format($row['orderTotalAmount']) . "원</td>";
                        echo "<td>" . $row['orderPaymentMethod'] . "</td>";
                        echo "<td>" . $row['storeName'] . "</td>";
                        
                        // 상태에 따라 색상과 문구 변경
                        if($row['orderStatus'] == 'READY') {
                            echo "<td class='status-ready'>픽업 대기중</td>";
                        } elseif ($row['orderStatus'] == 'PICKED_UP') {
                            echo "<td class='status-done'>수령 완료</td>";
                        } else {
                            echo "<td>" . $row['orderStatus'] . "</td>";
                        }

                        // 픽업 코드가 있으면 강조 표시
                        if($row['orderPickupCode']) {
                            echo "<td><span class='pickup-code'>" . $row['orderPickupCode'] . "</span></td>";
                        } else {
                            echo "<td>-</td>";
                        }
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='6'>주문 내역이 없습니다.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <br>
        <a href="main.php" style="text-decoration:none; color:#1e3a8a; font-weight:bold;">← 메인으로 돌아가기</a>
    </div>
</body>
</html>