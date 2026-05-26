<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 사용자 입력이 없는 정적 쿼리
$stores = $conn->query(
    "SELECT storeId, storeName, storeAddress, storePhoneNumber
     FROM P_STORE
     WHERE storeIsActive = 1 AND deletedAt IS NULL
     ORDER BY storeName"
);

$pageTitle = '매장 선택';
require 'header.php';
?>
<!-- Hero 배너: 매장 테마 -->
<div class="rounded-card overflow-hidden mb-8 relative h-40"
     style="background: linear-gradient(135deg, #fff0f3 0%, #ffe4e9 60%, #ffd9e1 100%);">
    <div class="absolute inset-0 flex items-center justify-around text-4xl opacity-25 select-none px-6">
        <span>🏪</span><span>🛒</span><span>🏬</span><span>🛍️</span><span>🏪</span><span>🛒</span>
    </div>
    <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-6">
        <h1 class="text-[22px] font-semibold text-ink tracking-tight">매장 선택</h1>
        <p class="text-ink/70 mt-1.5 text-sm font-medium">상품을 둘러볼 매장을 선택하세요.</p>
    </div>
</div>

<div class="grid sm:grid-cols-2 gap-4">
<?php while ($store = $stores->fetch_assoc()): ?>
    <a href="products.php?storeId=<?= (int)$store['storeId'] ?>"
       class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-2xl">🏪</div>
        <h2 class="text-base font-semibold text-ink mt-3"><?= h($store['storeName']) ?></h2>
        <p class="text-muted text-sm mt-1"><?= h($store['storeAddress']) ?></p>
        <p class="text-muted-soft text-xs mt-1">☎ <?= h($store['storePhoneNumber']) ?></p>
    </a>
<?php endwhile; ?>
</div>
<?php require 'footer.php'; ?>
