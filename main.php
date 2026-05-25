<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = (int)$_SESSION['user_id'];

// 카트의 현재 매장 — 한 번에 한 매장만 허용되므로 한 행만 확인하면 됨
$stmt = $conn->prepare("SELECT storeId FROM P_CART WHERE userId = ? LIMIT 1");
$stmt->bind_param("i", $userId);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();
$stmt->close();
$cartStoreId = $row ? (int)$row['storeId'] : 0;
$hasCart     = $cartStoreId > 0;

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
<h1 class="text-2xl font-bold text-blue-900">무엇을 도와드릴까요?</h1>
<p class="text-slate-600 mt-1"><?= h($_SESSION['user_name']) ?>님, CS24에 오신 것을 환영합니다.</p>

<div class="grid sm:grid-cols-2 gap-4 mt-8">
    <a href="fridge.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">🧊 나만의 냉장고</h3>
        <p class="text-slate-500 text-sm mt-1">구매한 증정품을 안전하게 보관하고 필요할 때 꺼내 드세요.</p>
    </a>
    <a href="stock.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">🔍 실시간 재고 찾기</h3>
        <p class="text-slate-500 text-sm mt-1">우리 동네 CS24 매장의 상품 재고를 실시간으로 확인합니다.</p>
    </a>
    <a href="orders.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">📦 주문 &amp; 픽업 내역</h3>
        <p class="text-slate-500 text-sm mt-1">내가 주문한 상품의 픽업 코드와 과거 내역을 조회합니다.</p>
    </a>
    <a href="stores.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">🧺 주문하기</h3>
        <p class="text-slate-500 text-sm mt-1">편의점 상품을 원격으로 주문합니다.</p>
    </a>
</div>

<?php if ($storeGroups): ?>
    <h2 class="text-2xl text-center font-bold text-blue-900 mt-12 mb-6">🔥 특가 세일 상품</h2>

    <div id="promoLayout" class="<?= $hasCart ? 'has-cart' : '' ?>">
        <div>
            <?php foreach ($storeGroups as $g): ?>
                <div class="mb-8">
                    <div class="flex items-center justify-between border-b border-slate-200 pb-2 mb-3">
                        <h3 class="text-lg font-bold text-slate-700"><?= h($g['storeName']) ?></h3>
                        <a href="products.php?storeId=<?= (int)$g['storeId'] ?>"
                           class="text-sm text-blue-700 hover:underline">더 주문하기</a>
                    </div>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                        <?php foreach ($g['products'] as $p): ?>
                            <div class="bg-white rounded-lg shadow p-4 flex flex-col">
                                <span class="text-xs bg-amber-100 text-amber-700 font-semibold px-2 py-0.5 rounded w-fit">
                                    <?= h($promotionLabels[$p['promotionType']] ?? '행사') ?>
                                </span>
                                <h4 class="font-bold text-slate-800 mt-2 flex-grow"><?= h($p['productName']) ?></h4>
                                <p class="text-blue-900 font-bold mt-1"><?= number_format((float)$p['productPrice']) ?>원</p>
                                <button type="button"
                                        class="promo-add-btn mt-3 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-2 rounded"
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
        <div id="cartContainer">
            <?php if ($hasCart) { $storeId = $cartStoreId; require 'cart_panel.php'; } ?>
        </div>
    </div>

    <style>
    @media (min-width: 1024px) {
        #promoLayout.has-cart {
            display: grid;
            grid-template-columns: 1fr 300px;
            gap: 1.5rem;
        }
    }
    #cartToast, #crossStoreBanner {
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        color: white;
        padding: 12px 24px;
        border-radius: 9999px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 50;
        font-weight: 600;
        pointer-events: none;
        animation: cartToast 2.5s ease-out forwards;
    }
    #cartToast        { background: #10b981; }
    #crossStoreBanner { background: #ef4444; }
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
        if (res.status === 409) { showCrossStoreBanner(); return; }
        if (!res.ok) return;

        // 카트 패널 표시(2열 레이아웃) 활성화 + 본문 갱신
        document.getElementById('promoLayout').classList.add('has-cart');
        await refreshCartPanel(storeId);
        showCartToast();
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
            await refreshCartPanel(sid);
        }
    });
    </script>
<?php endif; ?>
<?php require 'footer.php'; ?>
