<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// 픽업 인증 코드 생성 (혼동되는 글자 0,O,1,I,L 제외)
function generate_pickup_code() {
    $alphabet = 'ABCDEFGHJKMNPQRSTUVWXYZ23456789';
    $code = '';
    for ($i = 0; $i < 6; $i++) {
        $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
    }
    return $code;
}

// 행사 상품의 증정 수량 계산 (1+1: 구매수량, 2+1: 구매수량의 절반)
function bonus_quantity($promotionType, $quantity) {
    if ($promotionType === 'ONE_PLUS_ONE') {
        return $quantity;
    }
    if ($promotionType === 'TWO_PLUS_ONE') {
        return intdiv($quantity, 2);
    }
    return 0;
}

$userId  = (int)$_SESSION['user_id'];
$storeId = (int)($_GET['storeId'] ?? $_POST['storeId'] ?? 0);
$error   = '';

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

// ---- 주문 확정 처리 (트랜잭션) ----
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bonusOptIn     = array_map('intval', $_POST['store_bonus'] ?? []);
    $isDelivery     = (($_POST['order_type'] ?? 'pickup') === 'delivery');
    $recipientName  = trim($_POST['recipient_name']  ?? '');
    $recipientPhone = trim($_POST['recipient_phone'] ?? '');
    $deliveryAddr   = trim($_POST['delivery_addr']   ?? '');
    $deliveryDetail = trim($_POST['delivery_detail'] ?? '');
    $deliveryMemo   = trim($_POST['delivery_memo']   ?? '');

    if ($isDelivery && ($recipientName === '' || $recipientPhone === '' || $deliveryAddr === '')) {
        $error = '배달 정보(받으실 분·연락처·주소)를 모두 입력해 주세요.';
    }

    if (!$error) {
    try {
        $conn->begin_transaction();

        // 1. 장바구니 라인 + 재고 조회
        $stmt = $conn->prepare(
            "SELECT c.productId, c.cartQuantity, p.productName, p.productPrice,
                    p.promotionType, i.inventoryQuantity
             FROM P_CART c
             JOIN P_PRODUCT p         ON p.productId = c.productId
             JOIN P_STORE_INVENTORY i ON i.storeId = c.storeId AND i.productId = c.productId
             WHERE c.userId = ? AND c.storeId = ?"
        );
        $stmt->bind_param("ii", $userId, $storeId);
        $stmt->execute();
        $cartLines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
        $stmt->close();

        if (!$cartLines) {
            throw new Exception('장바구니가 비어 있습니다.');
        }

        // 2. 재고 검증 + 3. 합계 계산
        $total = 0;
        foreach ($cartLines as $line) {
            if ($line['cartQuantity'] > $line['inventoryQuantity']) {
                throw new Exception($line['productName'] . '의 재고가 부족합니다.');
            }
            $total += $line['cartQuantity'] * $line['productPrice'];
        }

        // 4. 주문 유형 분기 + 5. 주문(P_ORDER) 생성
        if ($isDelivery) {
            $stmt = $conn->prepare(
                "INSERT INTO P_ORDER
                   (userId, storeId, orderTotalAmount, orderPaymentMethod, orderStatus,
                    orderIsDelivery, orderPaidAt)
                 VALUES (?, ?, ?, 'CARD', 'PAID', 1, NOW())"
            );
            $stmt->bind_param("iid", $userId, $storeId, $total);
        } else {
            $pickupCode = generate_pickup_code();
            $stmt = $conn->prepare(
                "INSERT INTO P_ORDER
                   (userId, storeId, orderTotalAmount, orderPaymentMethod, orderStatus,
                    orderIsDelivery, orderPickupCode, orderPaidAt)
                 VALUES (?, ?, ?, 'CARD', 'PAID', 0, ?, NOW())"
            );
            $stmt->bind_param("iids", $userId, $storeId, $total, $pickupCode);
        }
        $stmt->execute();
        $orderId = $conn->insert_id;
        $stmt->close();

        // 6. 주문 상세(P_ORDER_DETAIL) + 7. 재고 차감(P_STORE_INVENTORY)
        //    + 행사 상품 증정품을 나만의 냉장고(P_STORAGE)에 보관
        $detailStmt = $conn->prepare(
            "INSERT INTO P_ORDER_DETAIL
               (orderId, productId, orderDetailQuantity, orderDetailUnitPrice, orderDetailSubtotal)
             VALUES (?, ?, ?, ?, ?)"
        );
        $invStmt = $conn->prepare(
            "UPDATE P_STORE_INVENTORY
             SET inventoryQuantity = inventoryQuantity - ?
             WHERE storeId = ? AND productId = ? AND inventoryQuantity >= ?"
        );
        $storageStmt = $conn->prepare(
            "INSERT INTO P_STORAGE
               (userId, productId, orderDetailId, storageQuantity, storageStatus, storageExpireAt)
             VALUES (?, ?, ?, ?, 'AVAILABLE', DATE_ADD(NOW(), INTERVAL 30 DAY))"
        );
        foreach ($cartLines as $line) {
            $productId = (int)$line['productId'];
            $quantity  = (int)$line['cartQuantity'];
            $unitPrice = (float)$line['productPrice'];   // 주문 시점 단가 스냅샷
            $subtotal  = $quantity * $unitPrice;

            $detailStmt->bind_param("iiidd", $orderId, $productId, $quantity, $unitPrice, $subtotal);
            $detailStmt->execute();
            $orderDetailId = $conn->insert_id;

            $invStmt->bind_param("iiii", $quantity, $storeId, $productId, $quantity);
            $invStmt->execute();
            if ($invStmt->affected_rows !== 1) {
                throw new Exception($line['productName'] . '의 재고 차감에 실패했습니다.');
            }

            // 행사 상품을 '보관'으로 선택했으면 증정품을 P_STORAGE에 적재
            $bonus = bonus_quantity($line['promotionType'], $quantity);
            if ($bonus > 0 && in_array($productId, $bonusOptIn, true)) {
                $storageStmt->bind_param("iiii", $userId, $productId, $orderDetailId, $bonus);
                $storageStmt->execute();
            }
        }
        $detailStmt->close();
        $invStmt->close();
        $storageStmt->close();

        // 8. 배달 주문이면 P_DELIVERY에 배송 정보 기록
        if ($isDelivery) {
            $delStmt = $conn->prepare(
                "INSERT INTO P_DELIVERY
                   (orderId, deliveryRecipientName, deliveryPhoneNumber,
                    deliveryAddress, deliveryAddressDetail, deliveryRequestMemo)
                 VALUES (?, ?, ?, ?, ?, ?)"
            );
            $delStmt->bind_param("isssss", $orderId, $recipientName, $recipientPhone,
                                  $deliveryAddr, $deliveryDetail, $deliveryMemo);
            $delStmt->execute();
            $delStmt->close();
        }

        // 9. mock 결제 기록 (실제 PG 연동 없이 승인 처리)
        $txId = 'MOCK-' . strtoupper(bin2hex(random_bytes(6)));
        $stmt = $conn->prepare(
            "INSERT INTO P_PAYMENT
               (orderId, paymentMethod, paymentTransactionId, paymentPaidAmount,
                paymentStatus, paymentPgProvider, paymentApprovedAt)
             VALUES (?, 'CARD', ?, ?, 'APPROVED', 'MOCK', NOW())"
        );
        $stmt->bind_param("isd", $orderId, $txId, $total);
        $stmt->execute();
        $stmt->close();

        // 10. 해당 매장 장바구니 비우기
        $stmt = $conn->prepare("DELETE FROM P_CART WHERE userId = ? AND storeId = ?");
        $stmt->bind_param("ii", $userId, $storeId);
        $stmt->execute();
        $stmt->close();

        $conn->commit();
        header("Location: orders.php?orderId=$orderId&new=1");
        exit();
    } catch (Throwable $e) {
        $conn->rollback();
        $error = $e->getMessage();
    }
    } // end if (!$error)
}

// ---- 주문 확인 화면용 장바구니 요약 ----
$stmt = $conn->prepare(
    "SELECT c.cartQuantity, p.productId, p.productName, p.productPrice, p.promotionType
     FROM P_CART c
     JOIN P_PRODUCT p ON p.productId = c.productId
     WHERE c.userId = ? AND c.storeId = ?
     ORDER BY p.productName"
);
$stmt->bind_param("ii", $userId, $storeId);
$stmt->execute();
$lines = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

$total = 0;
foreach ($lines as $line) {
    $total += $line['cartQuantity'] * $line['productPrice'];
}

$pageTitle = '주문 확인';
require 'header.php';
?>
<div class="max-w-2xl mx-auto">
<h1 class="text-[22px] font-semibold text-ink tracking-tight">주문 확인</h1>
<p class="text-muted mt-2"><?= h($store['storeName']) ?>에서 주문</p>

<?php if ($error): ?>
    <div class="mt-4 text-error text-sm font-medium"><?= h($error) ?></div>
<?php endif; ?>

<?php if (!$lines): ?>
    <div class="bg-canvas border border-hairline rounded-card p-10 mt-8 text-center">
        <p class="text-muted">장바구니가 비어 있어 주문할 수 없습니다.</p>
        <a href="stores.php" class="inline-flex items-center justify-center h-12 mt-5 px-6 bg-rausch hover:bg-rausch-active text-white text-sm font-medium rounded-lg transition-colors">
            매장 보러 가기
        </a>
    </div>
<?php else: ?>
    <form action="checkout.php" method="POST">
        <input type="hidden" name="storeId" value="<?= (int)$store['storeId'] ?>">

        <!-- 주문 방식 선택 -->
        <div class="bg-canvas border border-hairline rounded-card mt-6 p-6">
            <p class="text-sm font-semibold text-ink mb-4">주문 방식</p>
            <div class="grid grid-cols-2 gap-3">
                <label class="flex items-center gap-3 cursor-pointer border border-hairline rounded-lg px-4 py-3 hover:border-ink transition-colors">
                    <input type="radio" name="order_type" value="pickup"
                           <?= (($_POST['order_type'] ?? 'pickup') !== 'delivery') ? 'checked' : '' ?>
                           class="accent-rausch">
                    <span class="text-sm font-medium text-ink">📦 픽업 (매장 수령)</span>
                </label>
                <label class="flex items-center gap-3 cursor-pointer border border-hairline rounded-lg px-4 py-3 hover:border-ink transition-colors">
                    <input type="radio" name="order_type" value="delivery"
                           <?= (($_POST['order_type'] ?? '') === 'delivery') ? 'checked' : '' ?>
                           class="accent-rausch">
                    <span class="text-sm font-medium text-ink">🛵 배달</span>
                </label>
            </div>
        </div>

        <!-- 배달 정보 폼 (배달 선택 시 표시) -->
        <div id="deliveryForm" class="bg-canvas border border-hairline rounded-card mt-4 p-6 space-y-4
                                      <?= (($_POST['order_type'] ?? '') !== 'delivery') ? 'hidden' : '' ?>">
            <h3 class="text-base font-semibold text-ink">배달 정보</h3>
            <div>
                <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">받으실 분</label>
                <input type="text" name="recipient_name" id="recipientName"
                       value="<?= h($_POST['recipient_name'] ?? $_SESSION['user_name']) ?>"
                       class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">연락처</label>
                <input type="text" name="recipient_phone" id="recipientPhone"
                       value="<?= h($_POST['recipient_phone'] ?? '') ?>"
                       placeholder="010-XXXX-XXXX"
                       class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">주소</label>
                <input type="text" name="delivery_addr" id="deliveryAddr"
                       value="<?= h($_POST['delivery_addr'] ?? '') ?>"
                       placeholder="배달 주소를 입력하세요"
                       class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">
                    상세주소 <span class="text-muted-soft font-normal normal-case">(선택)</span>
                </label>
                <input type="text" name="delivery_detail"
                       value="<?= h($_POST['delivery_detail'] ?? '') ?>"
                       placeholder="동·호수 등"
                       class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
            </div>
            <div>
                <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">
                    요청사항 <span class="text-muted-soft font-normal normal-case">(선택)</span>
                </label>
                <input type="text" name="delivery_memo"
                       value="<?= h($_POST['delivery_memo'] ?? '') ?>"
                       placeholder="예: 문 앞에 놓아주세요"
                       class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
            </div>
        </div>

        <div class="bg-canvas border border-hairline rounded-card mt-4 divide-y divide-hairline-soft">
            <?php foreach ($lines as $line):
                $subtotal = $line['cartQuantity'] * $line['productPrice'];
                $bonus = bonus_quantity($line['promotionType'], (int)$line['cartQuantity']);
            ?>
            <div class="p-5">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="font-semibold text-ink"><?= h($line['productName']) ?></p>
                        <p class="text-sm text-muted mt-0.5">
                            <?= number_format((float)$line['productPrice']) ?>원 &times; <?= (int)$line['cartQuantity'] ?>개
                        </p>
                    </div>
                    <div class="font-semibold text-ink"><?= number_format($subtotal) ?>원</div>
                </div>
                <?php if ($bonus > 0): ?>
                    <label class="mt-3 flex items-center gap-2 text-sm text-ink bg-surface-soft rounded-lg px-3 py-2.5 cursor-pointer">
                        <input type="checkbox" name="store_bonus[]" value="<?= (int)$line['productId'] ?>" class="accent-rausch">
                        🧊 증정품 <?= $bonus ?>개를 나만의 냉장고에 보관
                    </label>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>

        <div class="bg-canvas border border-hairline rounded-card mt-4 p-6 flex items-center justify-between">
            <span class="text-sm text-muted">결제 예정 금액</span>
            <span class="text-[22px] font-semibold text-ink tracking-tight"><?= number_format($total) ?>원</span>
        </div>

        <p class="text-xs text-muted-soft mt-3">* 결제는 모의(mock) 처리되며 실제 결제는 발생하지 않습니다.</p>

        <button type="submit"
                class="w-full h-12 bg-rausch hover:bg-rausch-active text-white font-medium rounded-lg mt-4 transition-colors">
            주문 확정 &amp; 결제하기
        </button>
    </form>
    <a href="products.php?storeId=<?= (int)$store['storeId'] ?>"
       class="block text-center text-sm text-muted mt-4 hover:text-ink underline underline-offset-4">← 상품 보기로 돌아가기</a>
<?php endif; ?>
</div>
<script>
(function () {
    const form      = document.getElementById('deliveryForm');
    const radios    = document.querySelectorAll('input[name="order_type"]');
    const required  = ['recipientName', 'recipientPhone', 'deliveryAddr'];

    function sync() {
        const isDelivery = document.querySelector('input[name="order_type"]:checked')?.value === 'delivery';
        form.classList.toggle('hidden', !isDelivery);
        required.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.required = isDelivery;
        });
    }

    radios.forEach(r => r.addEventListener('change', sync));
    sync(); // 초기 상태 적용 (유효성 오류로 폼 재표시 시 라디오 상태 반영)
})();
</script>
<?php require 'footer.php'; ?>
