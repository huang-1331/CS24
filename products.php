<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$storeId = (int)($_GET['storeId'] ?? 0);

// 매장 유효성 확인
$stmt = $conn->prepare(
    "SELECT storeId, storeName
     FROM P_STORE
     WHERE storeId = ? AND storeIsActive = 1 AND deletedAt IS NULL"
);
$stmt->bind_param("i", $storeId);
$stmt->execute();
$store = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$store) {
    header('Location: stores.php');
    exit();
}

// 해당 매장이 판매(재고 보유)하는 상품 목록
$stmt = $conn->prepare(
    "SELECT p.productId, p.productName, p.productPrice, p.promotionType,
            c.categoryName, i.inventoryQuantity
     FROM P_STORE_INVENTORY i
     JOIN P_PRODUCT p  ON p.productId = i.productId
     JOIN P_CATEGORY c ON c.categoryId = p.categoryId
     WHERE i.storeId = ? AND p.productIsActive = 1 AND p.deletedAt IS NULL
     ORDER BY c.categoryDisplayOrder, p.productName"
);
$stmt->bind_param("i", $storeId);
$stmt->execute();
$all = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// 행사 상품(promotionType != NONE)을 위쪽 섹션으로 분리
$promo   = [];
$regular = [];
foreach ($all as $p) {
    if ($p['promotionType'] !== 'NONE') {
        $promo[] = $p;
    } else {
        $regular[] = $p;
    }
}

$promotionLabels = [
    'ONE_PLUS_ONE' => '1+1',
    'TWO_PLUS_ONE' => '2+1',
    'DISCOUNT'     => '할인',
];

function render_product_card($p, $storeId, $promotionLabels) {
    $stock   = (int)$p['inventoryQuantity'];
    $soldOut = $stock <= 0;
    ?>
    <div class="card-hover bg-canvas border border-hairline rounded-card p-5 flex flex-col">
        <div class="flex items-start justify-between">
            <span class="text-[11px] font-semibold uppercase tracking-wide text-muted"><?= h($p['categoryName']) ?></span>
            <?php if ($p['promotionType'] !== 'NONE'): ?>
                <span class="text-[11px] font-semibold text-ink bg-canvas border border-hairline shadow-sm px-2 py-0.5 rounded-full">
                    <?= h($promotionLabels[$p['promotionType']] ?? '행사') ?>
                </span>
            <?php endif; ?>
        </div>
        <h3 class="font-semibold text-ink mt-2"><?= h($p['productName']) ?></h3>
        <p class="text-ink font-medium mt-1"><?= number_format((float)$p['productPrice']) ?>원</p>
        <p class="text-xs mt-1 <?= $soldOut ? 'text-error font-medium' : 'text-muted' ?>">
            <?= $soldOut ? '품절' : '재고 ' . $stock . '개' ?>
        </p>

        <?php if ($soldOut): ?>
            <button disabled
                    class="mt-3 h-10 bg-surface-soft text-muted-soft text-sm font-medium rounded-lg cursor-not-allowed">
                품절
            </button>
        <?php else: ?>
            <form action="cart_process.php" method="POST" class="mt-3 flex gap-2 add-to-cart-form">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="storeId" value="<?= (int)$storeId ?>">
                <input type="hidden" name="productId" value="<?= (int)$p['productId'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= $stock ?>"
                       class="w-16 h-10 border border-hairline rounded-lg px-2 text-sm text-center">
                <button type="submit"
                        class="flex-grow h-10 bg-rausch hover:bg-rausch-active text-white text-sm font-medium rounded-lg transition-colors">
                    담기
                </button>
            </form>
        <?php endif; ?>
    </div>
    <?php
}

$pageTitle = $store['storeName'] . ' 상품';
require 'header.php';
?>
<div class="grid grid-cols-1 lg:grid-cols-[1fr_300px] gap-6">
    <div>
        <h1 class="text-[28px] leading-snug font-bold text-ink tracking-tight"><?= h($store['storeName']) ?></h1>
        <p class="text-muted mt-2">상품을 장바구니에 담아 보세요.</p>

        <?php if ($promo): ?>
            <h2 class="text-[22px] font-semibold text-rausch/80 tracking-tight mt-10 mb-4">🔥 행사 중</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <?php foreach ($promo as $p) render_product_card($p, $store['storeId'], $promotionLabels); ?>
            </div>
        <?php endif; ?>

        <?php if ($regular): ?>
            <h2 class="text-[22px] font-semibold text-ink tracking-tight mt-12 mb-4">일반 상품</h2>
            <div class="grid sm:grid-cols-2 gap-4">
                <?php foreach ($regular as $p) render_product_card($p, $store['storeId'], $promotionLabels); ?>
            </div>
        <?php endif; ?>
    </div>

    <div id="cartContainer">
        <?php $storeId = (int)$store['storeId']; require 'cart_panel.php'; ?>
    </div>
</div>

<style>
#cartToast, #crossStoreBanner {
    position: fixed;
    bottom: 32px;
    left: 50%;
    transform: translateX(-50%);
    color: white;
    padding: 14px 24px;
    border-radius: 8px;
    box-shadow: rgba(0,0,0,0.02) 0 0 0 1px, rgba(0,0,0,0.04) 0 2px 6px 0, rgba(0,0,0,0.1) 0 4px 8px 0;
    z-index: 50;
    font-weight: 500;
    font-size: 14px;
    pointer-events: none;
    animation: cartToast 2.5s ease-out forwards;
}
#cartToast        { background: #222222; }
#crossStoreBanner { background: #c13515; }
@keyframes cartToast {
    0%   { opacity: 0; transform: translate(-50%, 20px); }
    10%  { opacity: 1; transform: translate(-50%, 0); }
    80%  { opacity: 1; transform: translate(-50%, 0); }
    100% { opacity: 0; transform: translate(-50%, 20px); }
}
</style>
<script>
const STORE_ID = <?= (int)$store['storeId'] ?>;

// 이벤트 위임(Event Delegation)을 적용하여 비동기 갱신 후에도 버튼 이벤트 유지
document.addEventListener('submit', async (e) => {
    if (e.target && e.target.classList.contains('add-to-cart-form')) {
        e.preventDefault();
        let res;
        try {
            res = await fetch('cart_process.php', {
                method: 'POST',
                body: new FormData(e.target),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        } catch (err) { return; }
        if (res.status === 409) { showCrossStoreBanner(); return; }
        if (!res.ok) return;
        showCartToast();
        refreshCartPanel();
    }
});

document.addEventListener('click', async (e) => {
    if (e.target && e.target.id === 'cartClearBtn') {
        if (!confirm('장바구니를 비우시겠습니까?')) return;
        const body = new URLSearchParams({ action: 'clear', storeId: STORE_ID });
        try {
            await fetch('cart_process.php', {
                method: 'POST',
                body,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        } catch (err) { return; }
        refreshCartPanel();
    }
});

async function refreshCartPanel() {
    try {
        const res = await fetch('cart_panel.php?storeId=' + STORE_ID);
        if (res.ok) {
            document.getElementById('cartContainer').innerHTML = await res.text();
        }
    } catch (err) {}
}

function showCartToast() {
    document.getElementById('cartToast')?.remove();
    const t = document.createElement('div');
    t.id = 'cartToast';
    t.textContent = '✓ 장바구니에 상품을 담았습니다.';
    document.body.appendChild(t);
    setTimeout(() => t.remove(), 2500);
}

function showCrossStoreBanner() {
    document.getElementById('crossStoreBanner')?.remove();
    const b = document.createElement('div');
    b.id = 'crossStoreBanner';
    b.textContent = '한 번에 하나의 점포에서만 주문할 수 있습니다.';
    document.body.appendChild(b);
    setTimeout(() => b.remove(), 2500);
}

// === [신규 추가] stock.php 연동 실시간 자동 담기 로직 ===
document.addEventListener('DOMContentLoaded', () => {
    const urlParams = new URLSearchParams(window.location.search);
    const autoAddId = urlParams.get('autoAddProductId');
    
    if (autoAddId) {
        // 해당 productId 값을 hidden input으로 가진 form 요소를 정확히 매칭
        const targetForm = document.querySelector(`.add-to-cart-form input[name="productId"][value="${autoAddId}"]`)?.closest('form');
        
        if (targetForm) {
            // 수량 1개 설정 후 강제 서브밋 트리거 (기존의 AJAX submit 리스너가 받아 처리함)
            const qtyInput = targetForm.querySelector('input[name="quantity"]');
            if (qtyInput) qtyInput.value = "1";
            
            targetForm.requestSubmit();
        }
        
        // 새로고침 시 무한 추가 현상 방지를 위해 URL 주소창에서 파라미터 깔끔하게 제거
        urlParams.delete('autoAddProductId');
        const newUrl = window.location.pathname + (urlParams.toString() ? '?' + urlParams.toString() : '');
        window.history.replaceState({}, document.title, newUrl);
    }
});

// 개별 상품 삭제 (❌ 버튼)
document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.cart-remove-btn');
    if (!btn) return;
    const body = new URLSearchParams({
        action: 'remove',
        storeId: btn.dataset.storeId,
        productId: btn.dataset.productId,
    });
    try {
        await fetch('cart_process.php', {
            method: 'POST', body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
    } catch (err) { return; }
    refreshCartPanel();
});

// 수량 input — 실시간 소계 갱신
document.addEventListener('input', (e) => {
    if (!e.target.classList.contains('cart-qty-input')) return;
    const qty = Math.max(1, parseInt(e.target.value, 10) || 1);
    const unitPrice = parseFloat(e.target.dataset.unitPrice) || 0;
    const subtotalEl = e.target.closest('li')?.querySelector('.cart-item-subtotal');
    if (subtotalEl) subtotalEl.textContent = Math.round(qty * unitPrice).toLocaleString('ko-KR') + '원';
    let total = 0;
    document.querySelectorAll('.cart-qty-input').forEach(inp => {
        total += Math.max(1, parseInt(inp.value, 10) || 1) * (parseFloat(inp.dataset.unitPrice) || 0);
    });
    const totalEl = document.getElementById('cartPanelTotal');
    if (totalEl) totalEl.textContent = Math.round(total).toLocaleString('ko-KR') + '원';
});

// 수량 input — 서버 반영 후 패널 갱신
document.addEventListener('change', async (e) => {
    if (!e.target.classList.contains('cart-qty-input')) return;
    const qty = Math.max(1, parseInt(e.target.value, 10) || 1);
    e.target.value = qty;
    const body = new URLSearchParams({
        action: 'update',
        storeId: e.target.dataset.storeId,
        productId: e.target.dataset.productId,
        quantity: qty,
    });
    try {
        await fetch('cart_process.php', {
            method: 'POST', body,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
    } catch (err) { return; }
    refreshCartPanel();
});

// === 휘발성 카트 이탈 가드 ===
// products.php 에서 다른 페이지로 이동 시 카트를 영구 저장하지 않는다.

let bypassUnloadGuard = false;

function cartHasItems() {
    return document.querySelector('#cartPanelBody ul') !== null;
}

async function clearCartViaXhr() {
    try {
        await fetch('cart_process.php', {
            method: 'POST',
            body: new URLSearchParams({ action: 'clear', storeId: STORE_ID }),
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
    } catch (err) {}
}

// in-page 링크 클릭 가드 (이벤트 위임). checkout.php 이동은 통과.
document.addEventListener('click', async (e) => {
    const link = e.target.closest && e.target.closest('a');
    if (!link) return;
    const href = link.getAttribute('href');
    if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
    if (href.includes('checkout.php')) { bypassUnloadGuard = true; return; }
    if (!cartHasItems()) return;
    e.preventDefault();
    if (confirm('담은 항목은 저장되지 않습니다. 계속하시겠습니까?')) {
        await clearCartViaXhr();
        window.location.href = href;
    }
});

// 닫기/새로고침/뒤로가기: 브라우저 표준 확인창
window.addEventListener('beforeunload', (e) => {
    if (bypassUnloadGuard) return;
    if (cartHasItems()) {
        e.preventDefault();
        e.returnValue = '';
    }
});

// 실제 이탈 시 best-effort clear
window.addEventListener('pagehide', () => {
    if (bypassUnloadGuard) return;
    if (cartHasItems()) {
        navigator.sendBeacon(
            'cart_process.php',
            new URLSearchParams({ action: 'clear', storeId: STORE_ID })
        );
    }
});
</script>

<?php require 'footer.php'; ?>