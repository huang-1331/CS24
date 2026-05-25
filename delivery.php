<?php
session_start();
include "db.php";
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }

// 장바구니 담기 처리
if(isset($_GET['add_id'])) {
    $id = $_GET['add_id'];
    $name = $_GET['add_name'];
    if(!isset($_SESSION['del_cart'][$id])) {
        $_SESSION['del_cart'][$id] = ['name' => $name, 'qty' => 1];
    } else {
        $_SESSION['del_cart'][$id]['qty']++;
    }
    header("Location: delivery.php"); // URL 깔끔하게 새로고침
    exit();
}

// 장바구니 비우기 처리
if(isset($_GET['clear'])) {
    unset($_SESSION['del_cart']);
    header("Location: delivery.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>스마트 배달</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; text-align: center; margin: 0; }
        header { background: #1e3a8a; color: white; padding: 20px; }
        .container { max-width: 800px; margin: 20px auto; background: white; padding: 30px; border-radius: 10px; text-align: left; }
        .flex-box { display: flex; gap: 20px; }
        .left-col, .right-col { flex: 1; background: #f9f9f9; padding: 20px; border-radius: 8px; border: 1px solid #ddd; }
        label { font-weight: bold; display: block; margin-top: 10px; }
        input[type="text"] { width: 100%; padding: 8px; margin-top: 5px; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        .btn { padding: 8px 12px; background: #1e3a8a; color: white; border: none; border-radius: 5px; cursor: pointer; }
        .btn-add { background: #10b981; }
        .btn-clear { background: #ef4444; padding: 5px 10px; font-size: 12px; float: right; }
        .submit-btn { width: 100%; padding: 15px; background: #f59e0b; color: white; border: none; border-radius: 5px; font-size: 16px; font-weight: bold; cursor: pointer; margin-top: 20px; }
        ul { list-style: none; padding: 0; }
        li { padding: 8px 0; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; }
    </style>
</head>
<body>
    <header>
        <h2>🛵 스마트 배달 주문</h2>
    </header>
    <div class="container">
        <div class="flex-box">
            <div class="left-col">
                <h3>🛒 배달 상품 담기</h3>
                <form method="GET" action="delivery.php" style="margin-bottom: 15px;">
                    <input type="text" name="search_item" placeholder="상품명 검색 (예: 콜라)" value="<?php echo isset($_GET['search_item']) ? htmlspecialchars($_GET['search_item']) : ''; ?>" required style="width: 70%;">
                    <button type="submit" class="btn">검색</button>
                </form>
                
                <?php
                if(isset($_GET['search_item']) && $_GET['search_item'] != '') {
                    $search = mysqli_real_escape_string($conn, $_GET['search_item']);
                    // 재고가 1개라도 있는 상품만 검색
                    $sql = "SELECT p.productId, p.productName, SUM(i.inventoryQuantity) as totalStock 
                            FROM P_PRODUCT p 
                            JOIN P_STORE_INVENTORY i ON p.productId = i.productId 
                            WHERE p.productName LIKE '%$search%' 
                            GROUP BY p.productId HAVING totalStock > 0";
                    $result = mysqli_query($conn, $sql);

                    if($result && mysqli_num_rows($result) > 0) {
                        echo "<ul>";
                        while($row = mysqli_fetch_assoc($result)) {
                            echo "<li>";
                            echo "<span>" . $row['productName'] . " (재고: " . $row['totalStock'] . ")</span>";
                            echo "<a href='delivery.php?add_id=".$row['productId']."&add_name=".urlencode($row['productName'])."'><button class='btn btn-add'>담기</button></a>";
                            echo "</li>";
                        }
                        echo "</ul>";
                    } else {
                        echo "<p style='color:red; font-size:14px;'>검색된 상품이 없거나 재고가 없습니다.</p>";
                    }
                }
                ?>
            </div>

            <div class="right-col">
                <h3>📦 담은 상품 
                    <?php if(isset($_SESSION['del_cart']) && count($_SESSION['del_cart']) > 0) { ?>
                        <a href="delivery.php?clear=1" class="btn btn-clear">모두 비우기</a>
                    <?php } ?>
                </h3>
                <ul>
                    <?php
                    if(isset($_SESSION['del_cart']) && count($_SESSION['del_cart']) > 0) {
                        foreach($_SESSION['del_cart'] as $item) {
                            echo "<li><span>" . $item['name'] . "</span> <span>" . $item['qty'] . "개</span></li>";
                        }
                    } else {
                        echo "<li>담은 상품이 없습니다.</li>";
                    }
                    ?>
                </ul>

                <hr style="margin: 20px 0; border: 0; border-top: 1px solid #ccc;">

                <form onsubmit="event.preventDefault(); alert('배달 요청이 접수되었습니다! 라이더가 배정 중입니다.'); window.location.href='main.php';">
                    <label>받으실 분</label>
                    <input type="text" value="<?php echo isset($_SESSION['user_name']) ? $_SESSION['user_name'] : ''; ?>" required>
                    
                    <label>연락처</label>
                    <input type="text" placeholder="010-XXXX-XXXX" required>
                    
                    <label>배달 주소</label>
                    <input type="text" placeholder="예: 경희대학교 공과대학 2층" required>
                    
                    <button type="submit" class="submit-btn" <?php echo (!isset($_SESSION['del_cart']) || count($_SESSION['del_cart']) == 0) ? 'disabled style="background:#ccc;"' : ''; ?>>
                        배달 요청하기
                    </button>
                </form>
            </div>
        </div>
        <br>
        <div style="text-align:center;">
            <a href="main.php" style="text-decoration:none; color:#1e3a8a; font-weight:bold;">← 메인으로 돌아가기</a>
        </div>
    </div>
</body>
</html>