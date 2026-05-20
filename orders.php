<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId  = (int)$_SESSION['user_id'];
$orderId = (int)($_GET['orderId'] ?? 0);
$isNew   = ($_GET['new'] ?? '') === '1';

$statusLabels = [
    'PENDING'   => '결제 대기',
    'PAID'      => '결제 완료',
    'READY'     => '픽업 준비',
    'PICKED_UP' => '픽업 완료',
    'CANCELED'  => '주문 취소',
];

$order   = null;
$details = [];
if ($orderId > 0) {
    // 주문 단건 조회 (본인 주문만)
    $stmt = $conn->prepare(
        "SELECT o.orderId, o.orderTotalAmount, o.orderStatus, o.orderPickupCode,
                o.orderPaidAt, o.createdAt, s.storeName
         FROM P_ORDER o
         JOIN P_STORE s ON s.storeId = o.storeId
         WHERE o.orderId = ? AND o.userId = ?"
    );
    $stmt->bind_param("ii", $orderId, $userId);
    $stmt->execute();
    $order = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    if ($order) {
        $stmt = $conn->prepare(
            "SELECT od.orderDetailQuantity, od.orderDetailUnitPrice, od.orderDetailSubtotal,
                    p.productName
             FROM P_ORDER_DETAIL od
             JOIN P_PRODUCT p ON p.productId = od.productId
             WHERE od.orderId = ?"
        );
        $stmt->bind_param("i", $orderId);
        $stmt->execute();
        $details = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();
    }
}

$pageTitle = $order ? '주문 상세' : '주문 내역';
require 'header.php';
?>
<?php if ($orderId > 0): ?>
    <?php if (!$order): ?>
        <div class="bg-white rounded-lg shadow p-8 text-center">
            <p class="text-slate-500">주문을 찾을 수 없습니다.</p>
            <a href="orders.php" class="inline-block mt-4 bg-blue-900 text-white text-sm font-semibold px-4 py-2 rounded">
                주문 내역으로
            </a>
        </div>
    <?php else: ?>
        <?php if ($isNew): ?>
            <div class="bg-green-100 text-green-800 rounded-lg px-4 py-3 mb-4">
                ✅ 주문이 완료되었습니다. 아래 픽업 코드를 매장에 보여주세요.
            </div>
        <?php endif; ?>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center justify-between">
                <h1 class="text-xl font-bold text-blue-900">주문 #<?= (int)$order['orderId'] ?></h1>
                <span class="text-sm bg-blue-100 text-blue-800 font-semibold px-3 py-1 rounded">
                    <?= h($statusLabels[$order['orderStatus']] ?? $order['orderStatus']) ?>
                </span>
            </div>
            <p class="text-slate-500 text-sm mt-1"><?= h($order['storeName']) ?> &middot; <?= h($order['createdAt']) ?></p>

            <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mt-4 text-center">
                <p class="text-sm text-amber-700">픽업 코드</p>
                <p class="text-3xl font-bold tracking-widest text-amber-800 mt-1">
                    <?= h($order['orderPickupCode']) ?>
                </p>
            </div>

            <div class="mt-5 divide-y border-t border-b">
                <?php foreach ($details as $d): ?>
                <div class="py-3 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-slate-800"><?= h($d['productName']) ?></p>
                        <p class="text-sm text-slate-400">
                            <?= number_format((float)$d['orderDetailUnitPrice']) ?>원 &times;
                            <?= (int)$d['orderDetailQuantity'] ?>개
                        </p>
                    </div>
                    <div class="font-bold text-blue-900"><?= number_format((float)$d['orderDetailSubtotal']) ?>원</div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center justify-between mt-4">
                <span class="text-lg font-semibold text-slate-700">결제 금액</span>
                <span class="text-2xl font-bold text-blue-900"><?= number_format((float)$order['orderTotalAmount']) ?>원</span>
            </div>
        </div>

        <a href="orders.php" class="block text-center text-sm text-slate-500 mt-4 hover:underline">← 전체 주문 내역</a>
    <?php endif; ?>

<?php else: ?>
    <h1 class="text-2xl font-bold text-blue-900">주문 내역</h1>
    <?php
    $stmt = $conn->prepare(
        "SELECT o.orderId, o.orderTotalAmount, o.orderStatus, o.orderPickupCode,
                o.createdAt, s.storeName
         FROM P_ORDER o
         JOIN P_STORE s ON s.storeId = o.storeId
         WHERE o.userId = ?
         ORDER BY o.orderId DESC"
    );
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    $stmt->close();
    ?>
    <?php if (!$orders): ?>
        <div class="bg-white rounded-lg shadow p-8 mt-6 text-center">
            <p class="text-slate-500">아직 주문 내역이 없습니다.</p>
            <a href="stores.php" class="inline-block mt-4 bg-blue-900 text-white text-sm font-semibold px-4 py-2 rounded">
                상품 주문하러 가기
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-3 mt-6">
            <?php foreach ($orders as $o): ?>
            <a href="orders.php?orderId=<?= (int)$o['orderId'] ?>"
               class="block bg-white rounded-lg shadow p-4 hover:shadow-md transition">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-blue-900">주문 #<?= (int)$o['orderId'] ?></span>
                    <span class="text-xs bg-blue-100 text-blue-800 font-semibold px-2 py-1 rounded">
                        <?= h($statusLabels[$o['orderStatus']] ?? $o['orderStatus']) ?>
                    </span>
                </div>
                <p class="text-sm text-slate-500 mt-1"><?= h($o['storeName']) ?> &middot; <?= h($o['createdAt']) ?></p>
                <div class="flex items-center justify-between mt-2">
                    <span class="text-sm text-slate-500">픽업코드 <span class="font-semibold tracking-wider text-amber-700"><?= h($o['orderPickupCode']) ?></span></span>
                    <span class="font-bold text-blue-900"><?= number_format((float)$o['orderTotalAmount']) ?>원</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
<?php require 'footer.php'; ?>
