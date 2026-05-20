<?php
require 'db.php';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$pageTitle = '메인';
require 'header.php';
?>
<h1 class="text-2xl font-bold text-blue-900">무엇을 도와드릴까요?</h1>
<p class="text-slate-600 mt-1"><?= h($_SESSION['user_name']) ?>님, CS24에 오신 것을 환영합니다.</p>

<div class="grid sm:grid-cols-2 gap-4 mt-8">
    <a href="stores.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">🏬 매장 &amp; 상품 보기</h3>
        <p class="text-slate-500 text-sm mt-1">매장을 선택하고 상품을 장바구니에 담아 주문합니다.</p>
    </a>
    <a href="orders.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">📦 주문 &amp; 픽업 내역</h3>
        <p class="text-slate-500 text-sm mt-1">과거 주문과 픽업 코드를 확인합니다.</p>
    </a>
</div>
<?php require 'footer.php'; ?>
