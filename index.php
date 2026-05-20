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
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-bold text-blue-900">🏬 매장 선택</h3>
            <p class="text-slate-500 text-sm mt-1">가까운 CS24 매장을 골라 판매 상품을 확인합니다.</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-bold text-blue-900">🛒 장바구니</h3>
            <p class="text-slate-500 text-sm mt-1">원하는 상품을 담고 수량을 조절합니다.</p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
            <h3 class="font-bold text-blue-900">📦 주문 &amp; 픽업</h3>
            <p class="text-slate-500 text-sm mt-1">결제 후 발급되는 픽업 코드로 매장에서 수령합니다.</p>
        </div>
    </div>
</div>
<?php require 'footer.php'; ?>
