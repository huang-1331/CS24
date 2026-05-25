<?php
session_start();
include "db.php";

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}
$user_id = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>나만의 냉장고</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; text-align: center; margin: 0; }
        header { background: #1e3a8a; color: white; padding: 20px; }
        .container { max-width: 800px; margin: 30px auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; }
        th { background: #f0f4ff; color: #1e3a8a; }
        .btn { background: #f59e0b; color: white; border: none; padding: 5px 10px; border-radius: 5px; cursor: pointer; }
        .back-link { display: inline-block; margin-top: 20px; text-decoration: none; color: #1e3a8a; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h2>🧊 나만의 냉장고</h2>
    </header>
    <div class="container">
        <h3>보관 중인 상품</h3>
        <table>
            <thead>
                <tr>
                    <th>상품명</th>
                    <th>수량</th>
                    <th>유통기한(만료일)</th>
                    <th>상태</th>
                    <th>꺼내기</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $sql = "SELECT p.productName, s.storageQuantity, s.storageExpireAt, s.storageStatus 
                        FROM P_STORAGE s 
                        JOIN P_PRODUCT p ON s.productId = p.productId 
                        WHERE s.userId = '$user_id' AND s.storageStatus = 'AVAILABLE'";
                $result = mysqli_query($conn, $sql);

                if(mysqli_num_rows($result) > 0) {
                    while($row = mysqli_fetch_assoc($result)) {
                        echo "<tr>";
                        echo "<td>" . $row['productName'] . "</td>";
                        echo "<td>" . $row['storageQuantity'] . "개</td>";
                        echo "<td>" . $row['storageExpireAt'] . "</td>";
                        echo "<td>보관중</td>";
                        echo "<td><button class='btn' onclick=\"alert('상품을 꺼냈습니다! 가까운 매장에서 교환하세요.')\">바코드 보기</button></td>";
                        echo "</tr>";
                    }
                } else {
                    echo "<tr><td colspan='5'>현재 보관 중인 상품이 없습니다.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <a href="main.php" class="back-link">← 메인으로 돌아가기</a>
    </div>
</body>
</html>