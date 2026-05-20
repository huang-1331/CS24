<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId  = (int)$_SESSION['user_id'];
$storeId = (int)($_GET['storeId'] ?? 0);

// 매장 확인
$stmt = $conn->prepare(
    "SELECT storeId, storeName FROM P_STORE
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

// 장바구니 라인 (재고와 함께 조회해 부족 여부 표시)
$stmt = $conn->prepare(
    "SELECT c.cartId, c.cartQuantity, p.productName, p.productPrice, i.inventoryQuantity
     FROM P_CART c
     JOIN P_PRODUCT p          ON p.productId = c.productId
     JOIN P_STORE_INVENTORY i  ON i.storeId = c.storeId AND i.productId = c.productId
     WHERE c.userId = ? AND c.storeId = ?
     ORDER BY p.productName"
);
$stmt->bind_param("ii", $userId, $storeId);
$stmt->execute();
$lines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
$hasShortage = false;
foreach ($lines as $line) {
    $total += $line['cartQuantity'] * $line['productPrice'];
    if ($line['cartQuantity'] > $line['inventoryQuantity']) {
        $hasShortage = true;
    }
}

$pageTitle = '장바구니';
require 'header.php';
?>
<div class="flex items-center justify-between">
    <h1 class="text-2xl font-bold text-blue-900">🛒 장바구니</h1>
    <a href="products.php?storeId=<?= (int)$store['storeId'] ?>"
       class="text-sm text-blue-700 font-semibold hover:underline">← <?= h($store['storeName']) ?> 계속 쇼핑</a>
</div>

<?php if (!$lines): ?>
    <div class="bg-white rounded-lg shadow p-8 mt-6 text-center">
        <p class="text-slate-500">장바구니가 비어 있습니다.</p>
        <a href="products.php?storeId=<?= (int)$store['storeId'] ?>"
           class="inline-block mt-4 bg-blue-900 text-white text-sm font-semibold px-4 py-2 rounded">상품 보러 가기</a>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow mt-6 divide-y">
        <?php foreach ($lines as $line):
            $subtotal = $line['cartQuantity'] * $line['productPrice'];
            $shortage = $line['cartQuantity'] > $line['inventoryQuantity'];
        ?>
        <div class="p-4 flex flex-wrap items-center gap-3">
            <div class="flex-grow min-w-[150px]">
                <p class="font-semibold text-slate-800"><?= h($line['productName']) ?></p>
                <p class="text-sm text-slate-400"><?= number_format((float)$line['productPrice']) ?>원 / 개</p>
                <?php if ($shortage): ?>
                    <p class="text-xs text-red-500 font-semibold mt-1">
                        재고 부족 (현재 재고 <?= (int)$line['inventoryQuantity'] ?>개)
                    </p>
                <?php endif; ?>
            </div>
            <form action="cart_process.php" method="POST" class="flex items-center gap-1">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="storeId" value="<?= (int)$store['storeId'] ?>">
                <input type="hidden" name="cartId" value="<?= (int)$line['cartId'] ?>">
                <input type="number" name="quantity" value="<?= (int)$line['cartQuantity'] ?>" min="1"
                       class="w-16 border border-slate-300 rounded px-2 py-1 text-sm">
                <button type="submit"
                        class="bg-slate-200 hover:bg-slate-300 text-slate-700 text-sm px-2 py-1 rounded">변경</button>
            </form>
            <div class="w-24 text-right font-bold text-blue-900"><?= number_format($subtotal) ?>원</div>
            <form action="cart_process.php" method="POST">
                <input type="hidden" name="action" value="remove">
                <input type="hidden" name="storeId" value="<?= (int)$store['storeId'] ?>">
                <input type="hidden" name="cartId" value="<?= (int)$line['cartId'] ?>">
                <button type="submit"
                        class="text-red-500 hover:text-red-700 text-sm font-semibold">삭제</button>
            </form>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-lg shadow mt-4 p-5 flex items-center justify-between">
        <span class="text-lg font-semibold text-slate-700">합계</span>
        <span class="text-2xl font-bold text-blue-900"><?= number_format($total) ?>원</span>
    </div>

    <?php if ($hasShortage): ?>
        <p class="text-sm text-red-600 mt-3">재고가 부족한 상품이 있습니다. 수량을 조정해 주세요.</p>
    <?php else: ?>
        <a href="checkout.php?storeId=<?= (int)$store['storeId'] ?>"
           class="block mt-4 bg-amber-500 hover:bg-amber-600 text-white text-center font-bold py-3 rounded-lg">
            주문하기
        </a>
    <?php endif; ?>
<?php endif; ?>
<?php require 'footer.php'; ?>
