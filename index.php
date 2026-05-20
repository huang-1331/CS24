<?php
require 'db.php';
session_start();

// 이미 로그인했다면 회원 메인으로 이동
if (isset($_SESSION['user_id'])) {
    header('Location: main.php');
    exit();
}

$pageTitle = '환영합니다';
require 'header.php';
?>
<div class="text-center">
    <h1 class="text-3xl font-bold text-blue-900">CS24에 오신 것을 환영합니다 👋</h1>
    <p class="text-slate-600 mt-3">매장을 선택해 상품을 담고 주문하면 픽업 코드를 받습니다. (로그인 후 이용)</p>

    <div class="mt-8 flex justify-center gap-3">
        <a href="login.php" class="bg-amber-500 hover:bg-amber-600 text-white px-6 py-3 rounded-lg font-semibold">로그인</a>
        <a href="signup.php" class="bg-blue-900 hover:bg-blue-800 text-white px-6 py-3 rounded-lg font-semibold">회원가입</a>
    </div>

    <div class="grid sm:grid-cols-3 gap-4 mt-12 text-left">
        <a href="login.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
            <h3 class="text-lg font-bold text-blue-900">🧊 나만의 냉장고</h3>
            <p class="text-slate-500 text-sm mt-1">구매한 증정품을 안전하게 보관하고 필요할 때 꺼내 드세요.</p>
        </a>
        <a href="login.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
            <h3 class="text-lg font-bold text-blue-900">🔍 실시간 재고 찾기</h3>
            <p class="text-slate-500 text-sm mt-1">우리 동네 CS24 매장의 상품 재고를 실시간으로 확인합니다.</p>
        </a>
        <a href="login.php" class="block bg-white rounded-lg shadow p-6 hover:shadow-md hover:bg-blue-50 transition">
            <h3 class="text-lg font-bold text-blue-900">📦 주문 &amp; 픽업 내역</h3>
            <p class="text-slate-500 text-sm mt-1">내가 주문한 상품의 픽업 코드와 과거 내역을 조회합니다.</p>
        </a>
    </div>
</div>
<?php require 'footer.php'; ?>
