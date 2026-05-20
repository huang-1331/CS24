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
    try {
        $conn->begin_transaction();

        // 1. 장바구니 라인 + 재고 조회
        $stmt = $conn->prepare(
            "SELECT c.productId, c.cartQuantity, p.productName, p.productPrice, i.inventoryQuantity
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

        // 4. 픽업코드 + 5. 주문(P_ORDER) 생성
        $pickupCode = generate_pickup_code();
        $stmt = $conn->prepare(
            "INSERT INTO P_ORDER
               (userId, storeId, orderTotalAmount, orderPaymentMethod, orderStatus,
                orderIsDelivery, orderPickupCode, orderPaidAt)
             VALUES (?, ?, ?, 'CARD', 'PAID', 0, ?, NOW())"
        );
        $stmt->bind_param("iids", $userId, $storeId, $total, $pickupCode);
        $stmt->execute();
        $orderId = $conn->insert_id;
        $stmt->close();

        // 6. 주문 상세(P_ORDER_DETAIL) + 7. 재고 차감(P_STORE_INVENTORY)
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
        foreach ($cartLines as $line) {
            $productId = (int)$line['productId'];
            $quantity  = (int)$line['cartQuantity'];
            $unitPrice = (float)$line['productPrice'];   // 주문 시점 단가 스냅샷
            $subtotal  = $quantity * $unitPrice;

            $detailStmt->bind_param("iiidd", $orderId, $productId, $quantity, $unitPrice, $subtotal);
            $detailStmt->execute();

            $invStmt->bind_param("iiii", $quantity, $storeId, $productId, $quantity);
            $invStmt->execute();
            if ($invStmt->affected_rows !== 1) {
                throw new Exception($line['productName'] . '의 재고 차감에 실패했습니다.');
            }
        }
        $detailStmt->close();
        $invStmt->close();

        // 8. mock 결제 기록 (실제 PG 연동 없이 승인 처리)
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

        // 9. 해당 매장 장바구니 비우기
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
}

// ---- 주문 확인 화면용 장바구니 요약 ----
$stmt = $conn->prepare(
    "SELECT c.cartQuantity, p.productName, p.productPrice
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
<h1 class="text-2xl font-bold text-blue-900">주문 확인</h1>
<p class="text-slate-600 mt-1"><?= h($store['storeName']) ?>에서 픽업 주문</p>

<?php if ($error): ?>
    <div class="mt-4 bg-red-100 text-red-700 text-sm rounded px-4 py-2"><?= h($error) ?></div>
<?php endif; ?>

<?php if (!$lines): ?>
    <div class="bg-white rounded-lg shadow p-8 mt-6 text-center">
        <p class="text-slate-500">장바구니가 비어 있어 주문할 수 없습니다.</p>
        <a href="stores.php" class="inline-block mt-4 bg-blue-900 text-white text-sm font-semibold px-4 py-2 rounded">
            매장 보러 가기
        </a>
    </div>
<?php else: ?>
    <div class="bg-white rounded-lg shadow mt-6 divide-y">
        <?php foreach ($lines as $line):
            $subtotal = $line['cartQuantity'] * $line['productPrice'];
        ?>
        <div class="p-4 flex items-center justify-between">
            <div>
                <p class="font-semibold text-slate-800"><?= h($line['productName']) ?></p>
                <p class="text-sm text-slate-400">
                    <?= number_format((float)$line['productPrice']) ?>원 &times; <?= (int)$line['cartQuantity'] ?>개
                </p>
            </div>
            <div class="font-bold text-blue-900"><?= number_format($subtotal) ?>원</div>
        </div>
        <?php endforeach; ?>
    </div>

    <div class="bg-white rounded-lg shadow mt-4 p-5 flex items-center justify-between">
        <span class="text-lg font-semibold text-slate-700">결제 예정 금액</span>
        <span class="text-2xl font-bold text-blue-900"><?= number_format($total) ?>원</span>
    </div>

    <p class="text-xs text-slate-400 mt-2">* 결제는 모의(mock) 처리되며 실제 결제는 발생하지 않습니다.</p>

    <form action="checkout.php" method="POST" class="mt-4">
        <input type="hidden" name="storeId" value="<?= (int)$store['storeId'] ?>">
        <button type="submit"
                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-bold py-3 rounded-lg">
            주문 확정 &amp; 결제하기
        </button>
    </form>
    <a href="cart.php?storeId=<?= (int)$store['storeId'] ?>"
       class="block text-center text-sm text-slate-500 mt-3 hover:underline">← 장바구니로 돌아가기</a>
<?php endif; ?>
<?php require 'footer.php'; ?>
