<?php
// cart_panel.php - 사이드 카트 패널의 본문(라인 + 합계) HTML.
// 두 가지 호출 방식 모두 지원:
//   1) products.php 에서 require — 호출자가 $userId, $storeId, $conn 을 세팅함.
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

if (!$items) {
    echo '<p class="text-slate-400 text-sm text-center py-6">장바구니가 비어 있습니다.</p>';
    return;
}
?>
<ul class="divide-y">
<?php foreach ($items as $it): ?>
    <li class="py-2 flex items-center justify-between gap-2">
        <div class="flex-grow min-w-0">
            <p class="text-sm font-semibold text-slate-800 truncate"><?= h($it['productName']) ?></p>
            <p class="text-xs text-slate-400">
                <?= number_format((float)$it['productPrice']) ?>원 &times; <?= (int)$it['cartQuantity'] ?>개
            </p>
        </div>
        <div class="text-sm font-bold text-blue-900 whitespace-nowrap">
            <?= number_format((float)$it['cartQuantity'] * (float)$it['productPrice']) ?>원
        </div>
    </li>
<?php endforeach; ?>
</ul>
<div class="flex items-center justify-between mt-3 pt-2 border-t">
    <span class="text-sm font-semibold text-slate-700">합계</span>
    <span class="text-lg font-bold text-blue-900"><?= number_format($total) ?>원</span>
</div>
