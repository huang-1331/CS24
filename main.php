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
    <a href="fridge.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">🧊 나만의 냉장고</h3>
        <p class="text-slate-500 text-sm mt-1">구매한 증정품을 안전하게 보관하고 필요할 때 꺼내 드세요.</p>
    </a>
    <a href="stock.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">🔍 실시간 재고 찾기</h3>
        <p class="text-slate-500 text-sm mt-1">우리 동네 CS24 매장의 상품 재고를 실시간으로 확인합니다.</p>
    </a>
    <a href="orders.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">📦 주문 &amp; 픽업 내역</h3>
        <p class="text-slate-500 text-sm mt-1">내가 주문한 상품의 픽업 코드와 과거 내역을 조회합니다.</p>
    </a>
    <a href="stores.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
        <h3 class="text-lg font-bold text-blue-900">🧺 주문하기</h3>
        <p class="text-slate-500 text-sm mt-1">편의점 상품을 원격으로 주문합니다.</p>
    </a>
</div>
<?php require 'footer.php'; ?>
