<?php
// cart_panel.php - 장바구니 레이아웃 및 본문 통합 모듈
// 두 가지 호출 방식 모두 지원:
//   1) 타 파일에서 require — 호출자가 $userId, $storeId, $conn 을 세팅함.
//   2) AJAX 로 직접 호출 — 세션·DB 부트스트랩 후 $_GET['storeId'] 에서 읽음.

if (!isset($conn)) require __DIR__ . '/db.php';

// 행사 유형과 재고를 고려한 최대 구매 가능 수량
// ONE_PLUS_ONE: 1개 구매 → 2개 소비 → max = floor(stock / 2)
// TWO_PLUS_ONE: q구매 → q + floor(q/2) 소비 → max = floor((2*stock + 1) / 3)
if (!function_exists('cart_max_qty')) {
    function cart_max_qty(string $promoType, int $stock): int {
        if ($promoType === 'ONE_PLUS_ONE') return (int)floor($stock / 2);
        if ($promoType === 'TWO_PLUS_ONE') return (int)floor((2 * $stock + 1) / 3);
        return $stock;
    }
}
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    exit();
}
$userId = (int)$_SESSION['user_id'];
if (!isset($storeId)) {
    $storeId = (int)($_GET['storeId'] ?? 0);
}

$stmt = $conn->prepare(
    "SELECT c.cartQuantity, c.storeId, c.productId, p.productName, p.productPrice,
            p.promotionType, COALESCE(i.inventoryQuantity, 0) AS inventoryQuantity
     FROM P_CART c
     JOIN P_PRODUCT p ON p.productId = c.productId
     LEFT JOIN P_STORE_INVENTORY i ON i.storeId = c.storeId AND i.productId = c.productId
     WHERE c.userId = ?
     ORDER BY p.productName"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 실제 카트에 담긴 매장 기준으로 checkout/clear 링크 생성 (빈 카트면 요청된 storeId 사용)
$effectiveStoreId = !empty($items) ? (int)$items[0]['storeId'] : (int)$storeId;

$total = 0;
foreach ($items as $it) {
    $total += $it['cartQuantity'] * $it['productPrice'];
}
?>

<aside id="cartPanel" class="lg:sticky lg:top-1/2 lg:-translate-y-1/2 max-h-[80vh] flex flex-col bg-canvas border border-hairline rounded-card shadow-card p-6">
    <div class="flex items-center justify-between flex-shrink-0">
        <h3 class="text-base font-semibold text-ink">장바구니</h3>
        <button id="cartClearBtn" type="button"
                class="text-xs text-muted hover:text-ink underline underline-offset-4">
            비우기
        </button>
    </div>

    <div id="cartPanelBody" class="mt-4 flex-1 overflow-y-auto pr-1">
        <?php if (!$items): ?>
            <p class="text-muted text-sm text-center py-8">장바구니가 비어 있습니다.</p>
        <?php else: ?>
            <ul class="divide-y divide-hairline-soft">
            <?php foreach ($items as $it): ?>
                <?php $maxQty = cart_max_qty($it['promotionType'], (int)$it['inventoryQuantity']); ?>
                <li class="py-3 flex items-center gap-2">
                    <button type="button"
                            class="cart-remove-btn flex-shrink-0 text-muted-soft hover:text-error transition-colors text-xs leading-none"
                            data-product-id="<?= (int)$it['productId'] ?>"
                            data-store-id="<?= (int)$it['storeId'] ?>">❌</button>
                    <p class="text-sm font-medium text-ink truncate flex-grow min-w-0"><?= h($it['productName']) ?></p>
                    <input type="number"
                           class="cart-qty-input w-14 h-9 border border-hairline rounded-lg px-1 text-sm text-center flex-shrink-0"
                           value="<?= (int)$it['cartQuantity'] ?>" min="1" max="<?= $maxQty ?>"
                           data-product-id="<?= (int)$it['productId'] ?>"
                           data-store-id="<?= (int)$it['storeId'] ?>"
                           data-unit-price="<?= (float)$it['productPrice'] ?>">
                    <div class="text-sm font-semibold text-ink flex-shrink-0 cart-item-subtotal w-20 text-right">
                        <?= number_format((float)($it['cartQuantity'] * $it['productPrice'])) ?>원
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>

            <div class="border-t border-hairline pt-4 mt-4">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-muted">총 결제금액</span>
                    <span id="cartPanelTotal" class="text-[22px] font-semibold text-ink tracking-tight"><?= number_format((float)$total) ?>원</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <a href="checkout.php?storeId=<?= $effectiveStoreId ?>"
       class="flex items-center justify-center mt-5 h-12 bg-rausch hover:bg-rausch-active text-white font-medium rounded-lg flex-shrink-0 transition-colors">
        주문하기
    </a>
</aside>