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

<div class="max-w-4xl mx-auto">
    <!-- Hero 배너: 검색 테마 -->
    <div class="rounded-card overflow-hidden mb-8 relative h-44"
         style="background: linear-gradient(135deg, #fff0f3 0%, #ffe4e9 60%, #ffd9e1 100%);">
        <div class="absolute inset-0 flex items-center justify-around text-4xl opacity-25 select-none px-6">
            <span>🔍</span><span>📦</span><span>🛒</span><span>🏪</span><span>🔍</span><span>📦</span>
        </div>
        <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-6">
            <h1 class="text-[28px] leading-snug font-bold text-ink tracking-tight">실시간 재고 찾기</h1>
            <p class="text-ink/70 mt-2 text-sm font-medium">원하시는 상품의 이름을 검색하여 각 매장별 실시간 재고를 확인해 보세요.</p>
        </div>
    </div>

    <form method="GET" action="stock.php"
          class="flex items-center bg-canvas border border-hairline shadow-card rounded-full h-16 pl-6 pr-1.5 max-w-2xl mx-auto mb-10">
        <input type="text" name="keyword"
               placeholder="상품명을 입력하세요 (예: 삼각김밥, 콜라...)"
               value="<?= h($keyword) ?>"
               required
               class="flex-grow bg-transparent text-ink placeholder-muted-soft text-base focus:outline-none border-0"
               style="box-shadow:none;">
        <button type="submit"
                aria-label="재고 검색"
                class="flex items-center justify-center w-12 h-12 bg-rausch hover:bg-rausch-active rounded-full text-white flex-shrink-0 transition-colors">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
        </button>
    </form>

    <div>
        <?php if ($keyword === ''): ?>
            <div class="text-center py-16 bg-canvas border border-hairline rounded-card">
                <p class="text-muted">검색어를 입력해 주세요.</p>
            </div>
        <?php elseif (empty($groupedItems)): ?>
            <div class="text-center py-16 bg-canvas border border-hairline rounded-card">
                <p class="text-muted font-medium">"<?= h($keyword) ?>"에 대한 검색 결과가 없거나 모든 매장에서 재고가 소진되었습니다.</p>
            </div>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($groupedItems as $storeName => $products): ?>
                    <div class="bg-canvas border border-hairline rounded-card overflow-hidden">
                        <div class="px-6 py-4 border-b border-hairline flex justify-between items-center">
                            <h3 class="text-base font-semibold text-ink flex items-center gap-2">
                                🏪 <?= h($storeName) ?>
                            </h3>
                            <span class="text-xs bg-surface-soft text-ink px-3 py-1.5 rounded-full font-medium">
                                상품 <?= count($products) ?>건
                            </span>
                        </div>

                        <ul class="divide-y divide-hairline-soft px-6">
                            <?php foreach ($products as $p): ?>
                                <li class="py-4 flex items-center justify-between gap-4">
                                    <span class="font-medium text-ink truncate"><?= h($p['productName']) ?></span>

                                    <div class="flex items-center gap-3 flex-shrink-0">
                                        <?php if ($p['quantity'] <= 0): ?>
                                            <span class="text-xs text-muted font-medium bg-surface-soft px-3 py-1.5 rounded-full">
                                                품절
                                            </span>
                                        <?php elseif ($p['quantity'] >= 5): ?>
                                            <span class="text-xs text-ink font-medium bg-surface-soft px-3 py-1.5 rounded-full">
                                                재고 <?= $p['quantity'] ?>개
                                            </span>
                                        <?php else: ?>
                                            <span class="text-xs text-error font-medium bg-surface-soft px-3 py-1.5 rounded-full">
                                                재고 <?= $p['quantity'] ?>개 · 마감임박
                                            </span>
                                        <?php endif; ?>

                                        <a href="products.php?storeId=<?= $p['storeId'] ?>&autoAddProductId=<?= $p['productId'] ?>"
                                           class="bg-rausch hover:bg-rausch-active text-white text-xs font-medium px-4 py-2 rounded-full transition-colors">
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

    <div class="mt-10 text-center">
        <a href="main.php" class="inline-flex items-center text-ink font-medium underline underline-offset-4 hover:text-rausch transition-colors">
            ← 메인으로 돌아가기
        </a>
    </div>
</div>

<?php require 'footer.php'; ?>