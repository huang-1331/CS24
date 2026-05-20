<?php
// fridge_process.php - 나만의 냉장고에서 상품 꺼내기 처리 (HTML 출력 없이 redirect)
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: fridge.php');
    exit();
}

$userId    = (int)$_SESSION['user_id'];
$storageId = (int)($_POST['storageId'] ?? 0);

// 본인의 보관 중(AVAILABLE)·미만료 항목만 꺼낼 수 있다.
$stmt = $conn->prepare(
    "UPDATE P_STORAGE
     SET storageStatus = 'USED', storageRedeemedAt = NOW()
     WHERE storageId = ? AND userId = ? AND storageStatus = 'AVAILABLE'
           AND storageExpireAt >= NOW()"
);
$stmt->bind_param("ii", $storageId, $userId);
$stmt->execute();
$retrieved = $stmt->affected_rows === 1;
$stmt->close();

header('Location: fridge.php' . ($retrieved ? '?retrieved=1' : ''));
exit();
