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
<h1 class="text-2xl font-bold text-blue-900">🧊 나만의 냉장고</h1>
<p class="text-slate-600 mt-1">행사 상품 증정품을 보관하고 유효기간 내에 꺼내 사용하세요.</p>

<?php if ($retrieved): ?>
    <div class="mt-4 bg-green-100 text-green-800 rounded-lg px-4 py-3">
        ✅ 상품을 꺼냈습니다. 가까운 CS24 매장에서 수령하세요.
    </div>
<?php endif; ?>

<?php if (!$items): ?>
    <div class="bg-white rounded-lg shadow p-8 mt-6 text-center">
        <p class="text-slate-500">냉장고가 비어 있습니다. 1+1 등 행사 상품을 주문할 때 증정품을 보관할 수 있습니다.</p>
        <a href="stores.php" class="inline-block mt-4 bg-blue-900 text-white text-sm font-semibold px-4 py-2 rounded">
            상품 주문하러 가기
        </a>
    </div>
<?php else: ?>
    <div class="space-y-3 mt-6">
        <?php foreach ($items as $item):
            if ($item['storageStatus'] === 'USED') {
                $state = '사용 완료';
                $badge = 'bg-slate-200 text-slate-600';
            } elseif ($item['isExpired']) {
                $state = '기간 만료';
                $badge = 'bg-red-100 text-red-700';
            } else {
                $state = '보관 중';
                $badge = 'bg-blue-100 text-blue-800';
            }
            $canRetrieve = ($item['storageStatus'] === 'AVAILABLE' && !$item['isExpired']);
        ?>
        <div class="bg-white rounded-lg shadow p-4 flex flex-wrap items-center gap-3">
            <div class="flex-grow min-w-[160px]">
                <div class="flex items-center gap-2">
                    <p class="font-bold text-slate-800"><?= h($item['productName']) ?></p>
                    <span class="text-xs font-semibold px-2 py-0.5 rounded <?= $badge ?>"><?= $state ?></span>
                </div>
                <p class="text-sm text-slate-500 mt-1">보관 수량 <?= (int)$item['storageQuantity'] ?>개</p>
                <p class="text-xs text-slate-400 mt-1">
                    보관일 <?= h($item['storedDate']) ?> &middot; 유효기간 <?= h($item['expireDate']) ?>
                    <?php if ($canRetrieve): ?>(<?= (int)$item['daysLeft'] ?>일 남음)<?php endif; ?>
                </p>
            </div>
            <?php if ($canRetrieve): ?>
                <form action="fridge_process.php" method="POST">
                    <input type="hidden" name="storageId" value="<?= (int)$item['storageId'] ?>">
                    <button type="submit"
                            class="bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold px-4 py-2 rounded">
                        꺼내기
                    </button>
                </form>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
<?php require 'footer.php'; ?>
