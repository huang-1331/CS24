<?php
session_start();
include "db.php";
if(!isset($_SESSION['user_id'])) { header("Location: login.php"); exit(); }
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>실시간 재고 찾기</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; text-align: center; margin: 0; }
        header { background: #1e3a8a; color: white; padding: 20px; }
        .container { max-width: 800px; margin: 30px auto; background: white; padding: 20px; border-radius: 10px; }
        .search-box { margin-bottom: 20px; background: #f0f4ff; padding: 15px; border-radius: 8px; }
        input[type="text"] { padding: 10px; width: 50%; border: 1px solid #ccc; border-radius: 5px; }
        button { padding: 10px 20px; background: #1e3a8a; color: white; border: none; border-radius: 5px; cursor: pointer; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { padding: 12px; border-bottom: 1px solid #ddd; }
        th { background: #f0f4ff; color: #1e3a8a; }
        .stock-good { color: blue; font-weight: bold; }
        .stock-low { color: red; font-weight: bold; }
    </style>
</head>
<body>
    <header>
        <h2>🔍 실시간 재고 및 지점 찾기</h2>
    </header>
    <div class="container">
        <form class="search-box" method="GET" action="inventory.php">
            <label><input type="radio" name="search_type" value="product" <?php echo (!isset($_GET['search_type']) || $_GET['search_type'] == 'product') ? 'checked' : ''; ?>> 상품명으로 찾기</label>
            <label style="margin-left: 15px;"><input type="radio" name="search_type" value="store" <?php echo (isset($_GET['search_type']) && $_GET['search_type'] == 'store') ? 'checked' : ''; ?>> 지점명으로 찾기</label>
            <br><br>
            <input type="text" name="keyword" placeholder="검색어를 입력하세요..." value="<?php echo isset($_GET['keyword']) ? htmlspecialchars($_GET['keyword']) : ''; ?>" required>
            <button type="submit">검색</button>
        </form>

        <table>
            <thead>
                <tr>
                    <th>점포명</th>
                    <th>상품명</th>
                    <th>재고 수량</th>
                </tr>
            </thead>
            <tbody>
                <?php
                if(isset($_GET['keyword']) && $_GET['keyword'] != '') {
                    $keyword = mysqli_real_escape_string($conn, $_GET['keyword']);
                    $search_type = $_GET['search_type'];

                    // 검색 조건에 따라 SQL 쿼리 변경
                    if($search_type == 'store') {
                        $sql = "SELECT st.storeName, p.productName, i.inventoryQuantity 
                                FROM P_STORE_INVENTORY i 
                                JOIN P_STORE st ON i.storeId = st.storeId 
                                JOIN P_PRODUCT p ON i.productId = p.productId 
                                WHERE st.storeName LIKE '%$keyword%' AND i.inventoryQuantity > 0 
                                ORDER BY i.inventoryQuantity DESC";
                    } else {
                        $sql = "SELECT st.storeName, p.productName, i.inventoryQuantity 
                                FROM P_STORE_INVENTORY i 
                                JOIN P_STORE st ON i.storeId = st.storeId 
                                JOIN P_PRODUCT p ON i.productId = p.productId 
                                WHERE p.productName LIKE '%$keyword%' AND i.inventoryQuantity > 0 
                                ORDER BY i.inventoryQuantity DESC";
                    }
                    
                    $result = mysqli_query($conn, $sql);
                    
                    if($result && mysqli_num_rows($result) > 0) {
                        while($row = mysqli_fetch_assoc($result)) {
                            $stockClass = ($row['inventoryQuantity'] >= 5) ? 'stock-good' : 'stock-low';
                            echo "<tr>";
                            echo "<td>" . $row['storeName'] . "</td>";
                            echo "<td>" . $row['productName'] . "</td>";
                            echo "<td class='$stockClass'>" . $row['inventoryQuantity'] . "개</td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='3'>검색 결과가 없거나 재고가 모두 소진되었습니다.</td></tr>";
                    }
                } else {
                    echo "<tr><td colspan='3'>검색어를 입력해 주세요.</td></tr>";
                }
                ?>
            </tbody>
        </table>
        <br>
        <a href="main.php" style="text-decoration:none; color:#1e3a8a; font-weight:bold;">← 메인으로 돌아가기</a>
    </div>
</body>
</html>