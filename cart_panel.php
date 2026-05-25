<?php
// cart_panel.php - 장바구니 레이아웃 및 본문 통합 모듈
// 두 가지 호출 방식 모두 지원:
//   1) 타 파일에서 require — 호출자가 $userId, $storeId, $conn 을 세팅함.
//   2) AJAX 로 직접 호출 — 세션·DB 부트스트랩 후 $_GET['storeId'] 에서 읽음.

if (!isset($conn)) require __DIR__ . '/db.php';
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
    "SELECT c.cartQuantity, p.productName, p.productPrice
     FROM P_CART c
     JOIN P_PRODUCT p ON p.productId = c.productId
     WHERE c.userId = ? AND c.storeId = ?
     ORDER BY p.productName"
);
$stmt->bind_param("ii", $userId, $storeId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
foreach ($items as $it) {
    $total += $it['cartQuantity'] * $it['productPrice'];
}
?>

<aside id="cartPanel" class="lg:sticky lg:top-1/2 lg:-translate-y-1/2 max-h-[80vh] flex flex-col bg-white rounded-lg shadow p-4">
    <div class="flex items-center justify-between flex-shrink-0">
        <h3 class="font-bold text-blue-900">🛒 장바구니</h3>
        <button id="cartClearBtn" type="button"
                class="text-xs bg-slate-200 hover:bg-slate-300 text-slate-600 px-2 py-1 rounded">
            비우기
        </button>
    </div>
    
    <div id="cartPanelBody" class="mt-3 flex-1 overflow-y-auto pr-1">
        <?php if (!$items): ?>
            <p class="text-slate-400 text-sm text-center py-6">장바구니가 비어 있습니다.</p>
        <?php else: ?>
            <ul class="divide-y">
            <?php foreach ($items as $it): ?>
                <li class="py-2 flex items-center justify-between gap-2">
                    <div class="flex-grow min-w-0">
                        <p class="text-sm font-semibold text-slate-800 truncate"><?= h($it['productName']) ?></p>
                        <p class="text-xs text-slate-400">
                            <?= number_format((float)$it['productPrice']) ?>원 &times; <?= (int)$it['cartQuantity'] ?>개
                        </p>
                    </div>
                    <div class="text-sm font-bold text-blue-900 flex-shrink-0">
                        <?= number_format((float)($it['cartQuantity'] * $it['productPrice'])) ?>원
                    </div>
                </li>
            <?php endforeach; ?>
            </ul>

            <div class="border-t border-dashed border-slate-200 pt-3 mt-2">
                <div class="flex items-center justify-between font-bold text-slate-800">
                    <span>총 결제금액</span>
                    <span class="text-lg text-blue-900"><?= number_format((float)$total) ?>원</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
    
    <a href="checkout.php?storeId=<?= (int)$storeId ?>"
       class="block text-center mt-4 bg-amber-500 hover:bg-amber-600 text-white font-bold py-2 rounded flex-shrink-0">
        주문하기
    </a>
</aside>