<?php
// cart_process.php - 장바구니 담기/수정/삭제 처리 (HTML 출력 없이 redirect)
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

    // 같은 상품을 다시 담으면 수량을 합산한다 (UNIQUE: userId, storeId, productId).
    $stmt = $conn->prepare(
        "INSERT INTO P_CART (userId, storeId, productId, cartQuantity)
         VALUES (?, ?, ?, ?)
         ON DUPLICATE KEY UPDATE cartQuantity = cartQuantity + ?"
    );
    $stmt->bind_param("iiiii", $userId, $storeId, $productId, $quantity, $quantity);
    $stmt->execute();
    $stmt->close();

} elseif ($action === 'update') {
    $cartId   = (int)($_POST['cartId'] ?? 0);
    $quantity = max(1, (int)($_POST['quantity'] ?? 1));

    // cartId 와 userId 를 함께 조건으로 두어 타인의 장바구니는 수정할 수 없다.
    $stmt = $conn->prepare(
        "UPDATE P_CART SET cartQuantity = ? WHERE cartId = ? AND userId = ?"
    );
    $stmt->bind_param("iii", $quantity, $cartId, $userId);
    $stmt->execute();
    $stmt->close();

} elseif ($action === 'remove') {
    $cartId = (int)($_POST['cartId'] ?? 0);

    $stmt = $conn->prepare(
        "DELETE FROM P_CART WHERE cartId = ? AND userId = ?"
    );
    $stmt->bind_param("ii", $cartId, $userId);
    $stmt->execute();
    $stmt->close();
}

// AJAX 요청이면 본문 없이 204 — 페이지 이동 없음
if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
    http_response_code(204);
    exit();
}

// 비-AJAX 폴백: add 는 products.php 로 돌아가고, 수정/삭제는 기존대로 cart.php
$target = ($action === 'add')
    ? "products.php?storeId=$storeId&added=1"
    : "cart.php?storeId=$storeId";
header("Location: $target");
exit();
