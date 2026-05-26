<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId    = (int)$_SESSION['user_id'];
$retrieved = ($_GET['retrieved'] ?? '') === '1';

// 보관 중 → 기간 만료 → 사용 완료 순으로 정렬
$stmt = $conn->prepare(
    "SELECT s.storageId, s.storageQuantity, s.storageStatus,
            DATE_FORMAT(s.createdAt, '%Y-%m-%d') AS storedDate,
            DATE_FORMAT(s.storageExpireAt, '%Y-%m-%d') AS expireDate,
            (s.storageExpireAt < NOW()) AS isExpired,
            DATEDIFF(s.storageExpireAt, NOW()) AS daysLeft,
            p.productName
     FROM P_STORAGE s
     JOIN P_PRODUCT p ON p.productId = s.productId
     WHERE s.userId = ?
     ORDER BY CASE
                WHEN s.storageStatus = 'USED' THEN 3
                WHEN s.storageExpireAt < NOW() THEN 2
                ELSE 1
              END, s.storageExpireAt"
);
$stmt->bind_param("i", $userId);
$stmt->execute();
$items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$pageTitle = '나만의 냉장고';
require 'header.php';
?>
<div class="max-w-3xl mx-auto">
<!-- Hero 배너: 냉장고 테마 -->
<div class="rounded-card overflow-hidden mb-8 relative h-40"
     style="background: linear-gradient(135deg, #fff0f3 0%, #ffe4e9 60%, #ffd9e1 100%);">
    <div class="absolute inset-0 flex items-center justify-around text-4xl opacity-25 select-none px-6">
        <span>🧊</span><span>🥤</span><span>🍙</span><span>🍫</span><span>🧊</span><span>☕</span>
    </div>
    <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-6">
        <h1 class="text-[22px] font-semibold text-ink tracking-tight">🧊 나만의 냉장고</h1>
        <p class="text-ink/70 mt-1.5 text-sm font-medium">행사 상품 증정품을 보관하고 유효기간 내에 꺼내 사용하세요.</p>
    </div>
</div>

<?php if ($retrieved): ?>
    <div class="mt-5 bg-surface-soft text-ink rounded-lg px-5 py-4 text-sm font-medium">
        ✅ 상품을 꺼냈습니다. 가까운 CS24 매장에서 수령하세요.
    </div>
<?php endif; ?>

<?php if (!$items): ?>
    <div class="bg-canvas border border-hairline rounded-card p-10 mt-8 text-center">
        <p class="text-muted">냉장고가 비어 있습니다. 1+1 등 행사 상품을 주문할 때 증정품을 보관할 수 있습니다.</p>
        <a href="stores.php" class="inline-flex items-center justify-center h-12 mt-5 px-6 bg-rausch hover:bg-rausch-active text-white text-sm font-medium rounded-lg transition-colors">
            상품 주문하러 가기
        </a>
    </div>
<?php else: ?>
    <div class="space-y-3 mt-8">
        <?php foreach ($items as $item):
            if ($item['storageStatus'] === 'USED') {
                $state = '사용 완료';
                $badge = 'bg-surface-soft text-muted';
            } elseif ($item['isExpired']) {
                $state = '기간 만료';
                $badge = 'bg-surface-soft text-error';
            } else {
                $state = '보관 중';
                $badge = 'bg-surface-soft text-ink';
            }
            $canRetrieve = ($item['storageStatus'] === 'AVAILABLE' && !$item['isExpired']);
        ?>
        <div class="bg-canvas border border-hairline rounded-card p-5 flex flex-wrap items-center gap-4">
            <div class="flex-grow min-w-[160px]">
                <div class="flex items-center gap-2">
                    <p class="font-semibold text-ink"><?= h($item['productName']) ?></p>
                    <span class="text-xs font-medium px-2.5 py-1 rounded-full <?= $badge ?>"><?= $state ?></span>
                </div>
                <p class="text-sm text-muted mt-1">보관 수량 <?= (int)$item['storageQuantity'] ?>개</p>
                <p class="text-xs text-muted-soft mt-1">
                    보관일 <?= h($item['storedDate']) ?> &middot; 유효기간 <?= h($item['expireDate']) ?>
                    <?php if ($canRetrieve): ?>(<?= (int)$item['daysLeft'] ?>일 남음)<?php endif; ?>
                </p>
            </div>
            <?php if ($canRetrieve): ?>
                <form action="fridge_process.php" method="POST">
                    <input type="hidden" name="storageId" value="<?= (int)$item['storageId'] ?>">
                    <button type="submit"
                            class="h-10 px-5 bg-rausch hover:bg-rausch-active text-white text-sm font-medium rounded-lg transition-colors">
                        꺼내기
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
</div>
<?php require 'footer.php'; ?>
