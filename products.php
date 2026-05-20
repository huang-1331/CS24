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
$products = $stmt->get_result();
$stmt->close();

$promotionLabels = [
    'ONE_PLUS_ONE' => '1+1',
    'TWO_PLUS_ONE' => '2+1',
    'DISCOUNT'     => '할인',
];

$pageTitle = $store['storeName'] . ' 상품';
require 'header.php';
?>
<div class="flex items-center justify-between">
    <div>
        <h1 class="text-2xl font-bold text-blue-900"><?= h($store['storeName']) ?></h1>
        <p class="text-slate-600 mt-1">상품을 장바구니에 담아 보세요.</p>
    </div>
    <a href="cart.php?storeId=<?= (int)$store['storeId'] ?>"
       class="bg-blue-900 hover:bg-blue-800 text-white text-sm font-semibold px-4 py-2 rounded">🛒 장바구니</a>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4 mt-6">
<?php while ($p = $products->fetch_assoc()):
    $stock = (int)$p['inventoryQuantity'];
    $soldOut = $stock <= 0;
?>
    <div class="bg-white rounded-lg shadow p-5 flex flex-col">
        <div class="flex items-start justify-between">
            <span class="text-xs text-slate-400"><?= h($p['categoryName']) ?></span>
            <?php if ($p['promotionType'] !== 'NONE'): ?>
                <span class="text-xs bg-amber-100 text-amber-700 font-semibold px-2 py-0.5 rounded">
                    <?= h($promotionLabels[$p['promotionType']] ?? '행사') ?>
                </span>
            <?php endif; ?>
        </div>
        <h3 class="font-bold text-slate-800 mt-1"><?= h($p['productName']) ?></h3>
        <p class="text-blue-900 font-bold text-lg mt-1"><?= number_format((float)$p['productPrice']) ?>원</p>
        <p class="text-xs mt-1 <?= $soldOut ? 'text-red-500 font-semibold' : 'text-slate-400' ?>">
            <?= $soldOut ? '품절' : '재고 ' . $stock . '개' ?>
        </p>

        <?php if ($soldOut): ?>
            <button disabled
                    class="mt-3 bg-slate-200 text-slate-400 text-sm font-semibold py-2 rounded cursor-not-allowed">
                품절
            </button>
        <?php else: ?>
            <form action="cart_process.php" method="POST" class="mt-3 flex gap-2">
                <input type="hidden" name="action" value="add">
                <input type="hidden" name="storeId" value="<?= (int)$store['storeId'] ?>">
                <input type="hidden" name="productId" value="<?= (int)$p['productId'] ?>">
                <input type="number" name="quantity" value="1" min="1" max="<?= $stock ?>"
                       class="w-16 border border-slate-300 rounded px-2 py-1 text-sm">
                <button type="submit"
                        class="flex-grow bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold py-1 rounded">
                    담기
                </button>
            </form>
        <?php endif; ?>
    </div>
<?php endwhile; ?>
</div>
<?php require 'footer.php'; ?>
