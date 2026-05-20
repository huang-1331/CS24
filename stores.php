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
<h1 class="text-2xl font-bold text-blue-900">매장 선택</h1>
<p class="text-slate-600 mt-1">상품을 둘러볼 매장을 선택하세요.</p>

<div class="grid sm:grid-cols-2 gap-4 mt-6">
<?php while ($store = $stores->fetch_assoc()): ?>
    <a href="products.php?storeId=<?= (int)$store['storeId'] ?>"
       class="block bg-white rounded-lg shadow p-5 hover:shadow-md hover:bg-blue-50 transition">
        <h2 class="text-lg font-bold text-blue-900"><?= h($store['storeName']) ?></h2>
        <p class="text-slate-600 text-sm mt-1"><?= h($store['storeAddress']) ?></p>
        <p class="text-slate-400 text-xs mt-1">☎ <?= h($store['storePhoneNumber']) ?></p>
    </a>
<?php endwhile; ?>
</div>
<?php require 'footer.php'; ?>
