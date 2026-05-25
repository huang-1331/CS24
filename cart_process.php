<?php
// cart_process.php - 장바구니 담기/비우기 처리 (HTML 출력 없이 redirect 또는 204)
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: stores.php');
    exit();
}

$userId  = (int)$_SESSION['user_id'];
$action  = $_POST['action'] ?? '';
$storeId = (int)($_POST['storeId'] ?? 0);

if ($action === 'add') {
    $productId = (int)($_POST['productId'] ?? 0);
    $quantity  = max(1, (int)($_POST['quantity'] ?? 1));

    // 교차 매장 차단: 카트에 이미 다른 매장 항목이 있으면 거부 (한 번에 한 매장만 허용)
    $check = $conn->prepare(
        "SELECT 1 FROM P_CART WHERE userId = ? AND storeId <> ? LIMIT 1"
    );
    $check->bind_param("ii", $userId, $storeId);
    $check->execute();
    $hasOther = $check->get_result()->fetch_row() !== null;
    $check->close();

    if ($hasOther) {
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            http_response_code(409);
            exit();
        }
        header("Location: products.php?storeId=$storeId&conflict=1");
        exit();
    }

    // 같은 상품을 다시 담으면 수량을 합산 (UNIQUE: userId, storeId, productId).
    $stmt = $conn->prepare(
        "INSERT INTO P_CART (userId, storeId, productId, cartQuantity)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE cartQuantity = cartQuantity + ?"
    );
    $stmt->bind_param("iiiii", $userId, $storeId, $productId, $quantity, $quantity);
    $stmt->execute();
    $stmt->close();

} elseif ($action === 'clear') {
    // 해당 매장의 본인 장바구니를 전부 비우기.
    $stmt = $conn->prepare(
        "DELETE FROM P_CART WHERE userId = ? AND storeId = ?"
    );
    $stmt->bind_param("ii", $userId, $storeId);
    $stmt->execute();
    $stmt->close();
}

// AJAX 요청이면 본문 없이 204 — 페이지 이동 없음
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    http_response_code(204);
    exit();
}

// 비-AJAX 폴백: 어떤 액션이든 products.php 로 돌아감
$target = "products.php?storeId=$storeId" . ($action === 'add' ? '&added=1' : '');
header("Location: $target");
exit();
