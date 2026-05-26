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
                o.orderIsDelivery, o.orderPaidAt, o.createdAt, s.storeName
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
<div class="max-w-2xl mx-auto">
<?php if ($orderId > 0): ?>
    <?php if (!$order): ?>
        <div class="bg-canvas border border-hairline rounded-card p-10 text-center">
            <p class="text-muted">주문을 찾을 수 없습니다.</p>
            <a href="orders.php" class="inline-flex items-center justify-center h-12 mt-5 px-6 bg-rausch hover:bg-rausch-active text-white text-sm font-medium rounded-lg transition-colors">
                주문 내역으로
            </a>
        </div>
    <?php else: ?>
        <?php if ($isNew): ?>
            <div class="bg-surface-soft text-ink rounded-lg px-5 py-4 mb-5 text-sm font-medium">
                <?= $order['orderIsDelivery']
                    ? '✅ 주문이 완료되었습니다. 곧 배송이 시작됩니다.'
                    : '✅ 주문이 완료되었습니다. 아래 픽업 코드를 매장에 보여주세요.' ?>
            </div>
        <?php endif; ?>

        <div class="bg-canvas border border-hairline rounded-card p-8">
            <div class="flex items-center justify-between">
                <h1 class="text-[22px] font-semibold text-ink tracking-tight">주문 #<?= (int)$order['orderId'] ?></h1>
                <span class="text-xs bg-surface-soft text-ink font-medium px-3 py-1.5 rounded-full">
                    <?= h($statusLabels[$order['orderStatus']] ?? $order['orderStatus']) ?>
                </span>
            </div>
            <p class="text-muted text-sm mt-1"><?= h($order['storeName']) ?> &middot; <?= h($order['createdAt']) ?></p>

            <?php if ($order['orderIsDelivery']): ?>
                <div class="bg-surface-soft rounded-card p-8 mt-6 text-center">
                    <p class="text-xs uppercase tracking-wide text-muted">주문 유형</p>
                    <p class="text-[32px] font-bold text-ink mt-2 tracking-tight">🛵 배달 주문</p>
                </div>
            <?php else: ?>
                <div class="bg-surface-soft rounded-card p-8 mt-6 text-center">
                    <p class="text-xs uppercase tracking-wide text-muted">픽업 코드</p>
                    <p class="text-[64px] font-bold text-ink mt-2 leading-none tracking-[-0.02em]">
                        <?= h($order['orderPickupCode']) ?>
                    </p>
                </div>
            <?php endif; ?>

            <div class="mt-8 divide-y divide-hairline-soft border-t border-b border-hairline">
                <?php foreach ($details as $d): ?>
                <div class="py-4 flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-ink"><?= h($d['productName']) ?></p>
                        <p class="text-sm text-muted mt-0.5">
                            <?= number_format((float)$d['orderDetailUnitPrice']) ?>원 &times;
                            <?= (int)$d['orderDetailQuantity'] ?>개
                        </p>
                    </div>
                    <div class="font-semibold text-ink"><?= number_format((float)$d['orderDetailSubtotal']) ?>원</div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="flex items-center justify-between mt-6">
                <span class="text-sm text-muted">결제 금액</span>
                <span class="text-[22px] font-semibold text-ink tracking-tight"><?= number_format((float)$order['orderTotalAmount']) ?>원</span>
            </div>
        </div>

        <a href="orders.php" class="block text-center text-sm text-muted mt-5 hover:text-ink underline underline-offset-4">← 전체 주문 내역</a>
    <?php endif; ?>

<?php else: ?>
    <h1 class="text-[22px] font-semibold text-ink tracking-tight">주문 내역</h1>
    <?php
    $stmt = $conn->prepare(
        "SELECT o.orderId, o.orderTotalAmount, o.orderStatus, o.orderPickupCode,
                o.orderIsDelivery, o.createdAt, s.storeName
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
        <div class="bg-canvas border border-hairline rounded-card p-10 mt-6 text-center">
            <p class="text-muted">아직 주문 내역이 없습니다.</p>
            <a href="stores.php" class="inline-flex items-center justify-center h-12 mt-5 px-6 bg-rausch hover:bg-rausch-active text-white text-sm font-medium rounded-lg transition-colors">
                상품 주문하러 가기
            </a>
        </div>
    <?php else: ?>
        <div class="space-y-3 mt-6">
            <?php foreach ($orders as $o): ?>
            <a href="orders.php?orderId=<?= (int)$o['orderId'] ?>"
               class="card-hover block bg-canvas border border-hairline rounded-card p-5">
                <div class="flex items-center justify-between">
                    <span class="font-semibold text-ink">주문 #<?= (int)$o['orderId'] ?></span>
                    <span class="text-xs bg-surface-soft text-ink font-medium px-3 py-1.5 rounded-full">
                        <?= h($statusLabels[$o['orderStatus']] ?? $o['orderStatus']) ?>
                    </span>
                </div>
                <p class="text-sm text-muted mt-1"><?= h($o['storeName']) ?> &middot; <?= h($o['createdAt']) ?></p>
                <div class="flex items-center justify-between mt-3">
                    <?php if ($o['orderIsDelivery']): ?>
                        <span class="text-sm text-ink font-medium">🛵 배달</span>
                    <?php else: ?>
                        <span class="text-sm text-muted">픽업코드 <span class="font-semibold tracking-wider text-ink ml-1"><?= h($o['orderPickupCode']) ?></span></span>
                    <?php endif; ?>
                    <span class="font-semibold text-ink"><?= number_format((float)$o['orderTotalAmount']) ?>원</span>
                </div>
            </a>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
<?php endif; ?>
</div>
<?php require 'footer.php'; ?>
