<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];

// 매장별 행사 상품 조회 (재고 있는 것만)
$res = $conn->query(
    "SELECT s.storeId, s.storeName,
            p.productId, p.productName, p.productPrice, p.promotionType
     FROM P_STORE s
     JOIN P_STORE_INVENTORY i ON i.storeId = s.storeId
     JOIN P_PRODUCT p         ON p.productId = i.productId
     WHERE s.storeIsActive = 1 AND s.deletedAt IS NULL
       AND p.productIsActive = 1 AND p.deletedAt IS NULL
       AND p.promotionType <> 'NONE'
       AND i.inventoryQuantity > 0
     ORDER BY s.storeName, p.productName"
);
$promoRows = $res ? $res->fetch_all(MYSQLI_ASSOC) : [];

// storeId 로 그룹화
$storeGroups = [];
foreach ($promoRows as $r) {
    $sid = (int)$r['storeId'];
    if (!isset($storeGroups[$sid])) {
        $storeGroups[$sid] = [
            'storeId'   => $sid,
            'storeName' => $r['storeName'],
            'products'  => [],
        ];
    }
    $storeGroups[$sid]['products'][] = $r;
}

$promotionLabels = [
    'ONE_PLUS_ONE' => '1+1',
    'TWO_PLUS_ONE' => '2+1',
    'DISCOUNT'     => '할인',
];

$pageTitle = '메인';
require 'header.php';
?>
<!-- Hero 배너: emoji wallpaper + Rausch 계열 gradient -->
<div class="rounded-card overflow-hidden mb-10 relative h-56"
     style="background: linear-gradient(135deg, #fff0f3 0%, #ffe4e9 60%, #ffd9e1 100%);">
    <div class="absolute inset-0 flex items-center justify-around text-5xl opacity-25 select-none px-6">
        <span>🥤</span><span>🍙</span><span>🍜</span><span>🧊</span><span>☕</span><span>🍱</span><span>🍫</span><span>🥟</span>
    </div>
    <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-6">
        <h1 class="text-[28px] leading-snug font-bold text-ink tracking-tight">무엇을 도와드릴까요?</h1>
        <p class="text-ink/70 mt-2 font-medium"><?= h($_SESSION['user_name']) ?>님, CS24에 오신 것을 환영합니다.</p>
    </div>
</div>

<div class="grid sm:grid-cols-2 gap-4">
    <a href="fridge.php" class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-3xl">🧊</div>
        <h3 class="text-base font-semibold text-ink mt-3">나만의 냉장고</h3>
        <p class="text-muted text-sm mt-1 leading-relaxed">구매한 증정품을 안전하게 보관하고 필요할 때 꺼내 드세요.</p>
    </a>
    <a href="stock.php" class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-3xl">🔍</div>
        <h3 class="text-base font-semibold text-ink mt-3">실시간 재고 찾기</h3>
        <p class="text-muted text-sm mt-1 leading-relaxed">우리 동네 CS24 매장의 상품 재고를 실시간으로 확인합니다.</p>
    </a>
    <a href="orders.php" class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-3xl">📦</div>
        <h3 class="text-base font-semibold text-ink mt-3">주문 &amp; 픽업 내역</h3>
        <p class="text-muted text-sm mt-1 leading-relaxed">내가 주문한 상품의 픽업 코드와 과거 내역을 조회합니다.</p>
    </a>
    <a href="stores.php" class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-3xl">🧺</div>
        <h3 class="text-base font-semibold text-ink mt-3">주문하기</h3>
        <p class="text-muted text-sm mt-1 leading-relaxed">편의점 상품을 원격으로 주문합니다.</p>
    </a>
</div>

<?php if ($storeGroups): ?>
    <h2 class="text-[22px] font-semibold text-rausch/80 tracking-tight mt-16 mb-6 text-center">🔥 특가 세일 상품</h2>

    <div id="promoLayout">
        <?php foreach ($storeGroups as $g): ?>
            <div class="mb-10">
                <div class="flex items-center justify-between border-b border-hairline pb-3 mb-4">
                    <h3 class="text-base font-semibold text-ink"><?= h($g['storeName']) ?></h3>
                    <a href="products.php?storeId=<?= (int)$g['storeId'] ?>"
                       class="text-sm text-ink font-medium underline underline-offset-4 hover:text-rausch">더 주문하기 →</a>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                    <?php foreach ($g['products'] as $p): ?>
                        <div class="card-hover bg-canvas border border-hairline rounded-card p-4 flex flex-col">
                            <span class="text-[11px] font-semibold text-ink bg-canvas border border-hairline shadow-sm px-2 py-0.5 rounded-full w-fit">
                                <?= h($promotionLabels[$p['promotionType']] ?? '행사') ?>
                            </span>
                            <h4 class="font-semibold text-ink mt-3 flex-grow"><?= h($p['productName']) ?></h4>
                            <p class="text-ink font-medium mt-1"><?= number_format((float)$p['productPrice']) ?>원</p>
                            <button type="button"
                                    class="promo-add-btn mt-3 h-10 bg-rausch hover:bg-rausch-active text-white text-sm font-medium rounded-lg transition-colors"
                                    data-store-id="<?= (int)$g['storeId'] ?>"
                                    data-product-id="<?= (int)$p['productId'] ?>">
                                장바구니에 담기
                            </button>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div id="cartContainer" class="hidden"></div>

    <style>
    #cartContainer:not(.hidden) {
        position: fixed;
        top: 50%;
        right: 24px;
        transform: translateY(-50%);
        width: 300px;
        max-height: 85vh;
        z-index: 30;
    }
    /* fixed 부모 안에서는 cart_panel.php aside 의 sticky/transform 가 무의미하므로 무력화 */
    #cartContainer #cartPanel {
        position: static;
        transform: none;
    }
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
    // 카트 패널의 '주문하기' 링크 href 에서 현재 storeId 추출
    function getCurrentCartStoreId() {
        const link = document.querySelector('#cartContainer a[href*="checkout.php"]');
        if (!link) return null;
        const m = link.getAttribute('href').match(/storeId=(\d+)/);
        return m ? parseInt(m[1], 10) : null;
    }

    async function refreshCartPanel(storeId) {
        try {
            const res = await fetch('cart_panel.php?storeId=' + storeId);
            if (res.ok) {
                const c = document.getElementById('cartContainer');
                c.classList.remove('hidden');
                c.innerHTML = await res.text();
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

    // '장바구니에 담기' 클릭 (이벤트 위임)
    document.addEventListener('click', async (e) => {
        const btn = e.target.closest && e.target.closest('.promo-add-btn');
        if (!btn) return;
        const storeId   = btn.dataset.storeId;
        const productId = btn.dataset.productId;
        const body = new URLSearchParams({
            action: 'add', storeId, productId, quantity: 1
        });
        let res;
        try {
            res = await fetch('cart_process.php', {
                method: 'POST', body,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        } catch (err) { return; }
        if (res.status === 409) {
            showCrossStoreBanner();
            await refreshCartPanel(storeId);
            return;
        }
        if (!res.ok) return;

        await refreshCartPanel(storeId);
        showCartToast();
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
        await refreshCartPanel(btn.dataset.storeId);
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
        await refreshCartPanel(e.target.dataset.storeId);
    });

    // 카트 패널의 '비우기' 버튼 (패널이 동적으로 교체되므로 이벤트 위임)
    document.addEventListener('click', async (e) => {
        if (e.target && e.target.id === 'cartClearBtn') {
            const sid = getCurrentCartStoreId();
            if (!sid) return;
            if (!confirm('장바구니를 비우시겠습니까?')) return;
            const body = new URLSearchParams({ action: 'clear', storeId: sid });
            try {
                await fetch('cart_process.php', {
                    method: 'POST', body,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
            } catch (err) { return; }
            const c = document.getElementById('cartContainer');
            c.classList.add('hidden');
            c.innerHTML = '';
        }
    });

    // === 휘발성 카트 이탈 가드 ===
    // main.php 에서도 다른 페이지로 이동 시 카트를 영구 저장하지 않는다.
    // 면제: checkout.php(주문하기), products.php(더 주문하기) — 카트 보존 필요.

    let bypassUnloadGuard = false;

    function cartHasItems() {
        return document.querySelector('#cartPanelBody ul') !== null;
    }

    async function clearCartViaXhr() {
        const sid = getCurrentCartStoreId();
        if (!sid) return;
        try {
            await fetch('cart_process.php', {
                method: 'POST',
                body: new URLSearchParams({ action: 'clear', storeId: sid }),
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
        } catch (err) {}
    }

    // in-page 링크 클릭 가드 (이벤트 위임)
    document.addEventListener('click', async (e) => {
        const link = e.target.closest && e.target.closest('a');
        if (!link) return;
        const href = link.getAttribute('href');
        if (!href || href.startsWith('#') || href.startsWith('javascript:')) return;
        if (href.includes('checkout.php')) {
            bypassUnloadGuard = true;
            return;
        }
        if (href.includes('products.php')) {
            const cartStoreId = getCurrentCartStoreId();
            if (cartStoreId !== null) {
                const m = href.match(/storeId=(\d+)/);
                const targetStoreId = m ? parseInt(m[1], 10) : null;
                if (targetStoreId !== null && targetStoreId !== cartStoreId) {
                    e.preventDefault();
                    showCrossStoreBanner();
                    return;
                }
            }
            bypassUnloadGuard = true;
            return;
        }
        if (!cartHasItems()) return;
        e.preventDefault();
        if (confirm('담은 항목은 저장되지 않습니다. 계속하시겠습니까?')) {
            bypassUnloadGuard = true;      // 이미 처리하므로 브라우저 경고 중복 회피
            await clearCartViaXhr();
            window.location.href = href;
        }
    });

    // 닫기/새로고침/뒤로가기: 브라우저 표준 확인창(메시지 커스텀 불가)
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
            const sid = getCurrentCartStoreId();
            if (!sid) return;
            navigator.sendBeacon(
                'cart_process.php',
                new URLSearchParams({ action: 'clear', storeId: sid })
            );
        }
    });
    </script>
<?php endif; ?>
<?php require 'footer.php'; ?>
