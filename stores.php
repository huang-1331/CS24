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
<h1 class="text-[22px] font-semibold text-ink tracking-tight">매장 선택</h1>
<p class="text-muted mt-2">상품을 둘러볼 매장을 선택하세요.</p>

<div class="grid sm:grid-cols-2 gap-4 mt-8">
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
