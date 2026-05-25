<?php
session_start();
require 'db.php';

// 로그인 여부 확인
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = '실시간 재고 찾기';
require 'header.php';

$keyword = isset($_GET['keyword']) ? trim($_GET['keyword']) : '';
$groupedItems = [];

// 검색어가 존재할 때만 쿼리 실행
if ($keyword !== '') {
    // 상품명 매칭 및 재고가 있는 항목 조회 (Prepared Statement 안전하게 적용)
    $stmt = $conn->prepare(
        "SELECT st.storeId, st.storeName, p.productId, p.productName, i.inventoryQuantity 
         FROM P_STORE_INVENTORY i 
         JOIN P_STORE st ON i.storeId = st.storeId 
         JOIN P_PRODUCT p ON i.productId = p.productId 
         WHERE p.productName LIKE ? AND i.inventoryQuantity > 0 
         ORDER BY st.storeName, i.inventoryQuantity DESC"
    );
    
    $searchParam = "%{$keyword}%";
    $stmt->bind_param("s", $searchParam);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    // PHP단에서 매장(storeName)별로 데이터 그룹화 가공 (옵션 A 레이아웃용)
    foreach ($result as $row) {
        $groupedItems[$row['storeName']][] = [
            'storeId' => $row['storeId'], // 담기 기능 연동을 위한 storeId 유지
            'productId' => $row['productId'],
            'productName' => $row['productName'],
            'quantity' => (int)$row['inventoryQuantity']
        ];
    }
}
?>

<div class="max-w-4xl mx-auto my-8 px-4">
    <div class="text-center mb-8">
        <h1 class="text-3xl font-bold text-blue-900 flex items-center justify-center gap-2">
            🔍 실시간 재고 및 지점 찾기
        </h1>
        <p class="text-slate-600 mt-2">원하시는 상품의 이름을 검색하여 각 매장별 실시간 재고를 확인해 보세요.</p>
    </div>

    <div class="bg-blue-50 rounded-xl p-6 shadow-sm mb-8">
        <form method="GET" action="stock.php" class="flex flex-col sm:flex-row gap-3">
            <div class="relative flex-grow">
                <input type="text" name="keyword" 
                       placeholder="상품명을 입력하세요 (예: 삼각김밥, 콜라...)" 
                       value="<?= h($keyword) ?>" 
                       required
                       class="w-full px-4 py-3 rounded-lg border border-slate-300 focus:outline-none focus:ring-2 focus:ring-blue-500 text-slate-800">
            </div>
            <button type="submit" 
                    class="bg-blue-900 hover:bg-blue-800 text-white font-bold px-8 py-3 rounded-lg transition-colors flex-shrink-0">
                재고 검색
            </button>
        </form>
    </div>

    <div>
        <?php if ($keyword === ''): ?>
            <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-slate-100">
                <p class="text-slate-400">검색어를 입력해 주세요.</p>
            </div>
        <?php elseif (empty($groupedItems)): ?>
            <div class="text-center py-12 bg-white rounded-xl shadow-sm border border-slate-100">
                <p class="text-slate-500 font-medium">"<?= h($keyword) ?>"에 대한 검색 결과가 없거나 모든 매장에서 재고가 소진되었습니다.</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($groupedItems as $storeName => $products): ?>
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="bg-slate-50 px-5 py-3 border-b border-slate-200 flex justify-between items-center">
                            <h3 class="font-bold text-slate-800 flex items-center gap-2 text-lg">
                                🏪 <?= h($storeName) ?>
                            </h3>
                            <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded-full font-semibold">
                                상품 <?= count($products) ?>건
                            </span>
                        </div>
                        
                        <ul class="divide-y divide-slate-100 px-5">
                            <?php foreach ($products as $p): ?>
                                <li class="py-3.5 flex items-center justify-between gap-4">
                                    <span class="font-medium text-slate-700 truncate"><?= h($p['productName']) ?></span>
                                    
                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <?php if ($p['quantity'] <= 0): ?>
                                            <span class="text-xs text-slate-500 font-bold bg-slate-100 border border-slate-200 px-3 py-1 rounded">
                                                품절
                                            </span>
                                        <?php elseif ($p['quantity'] >= 5): ?>
                                            <span class="text-xs text-blue-600 font-bold bg-blue-50 border border-blue-100 px-3 py-1 rounded">
                                                재고 <?= $p['quantity'] ?>개 (여유)
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-red-500 font-bold bg-red-50 border border-red-100 px-3 py-1 rounded">
                                                재고 <?= $p['quantity'] ?>개 (마감임박)
                                            </span>
                                        <?php endif; ?>

                                        <a href="products.php?storeId=<?= $p['storeId'] ?>&autoAddProductId=<?= $p['productId'] ?>" 
                                           class="bg-amber-500 hover:bg-amber-600 text-white text-xs font-bold px-3 py-1.5 rounded transition-colors shadow-sm">
                                            담기
                                        </a>
                                    </div>
                                </li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <div class="mt-8 text-center">
        <a href="main.php" class="inline-flex items-center text-blue-900 hover:text-blue-700 font-bold transition-colors">
            ← 메인으로 돌아가기
        </a>
    </div>
</div>

<?php require 'footer.php'; ?>