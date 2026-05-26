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
<!-- Hero 배너: emoji wallpaper + Rausch 계열 gradient -->
<div class="rounded-card overflow-hidden relative h-72"
     style="background: linear-gradient(135deg, #fff0f3 0%, #ffe4e9 60%, #ffd9e1 100%);">
    <div class="absolute inset-0 flex items-center justify-around text-5xl opacity-25 select-none px-6">
        <span>🥤</span><span>🍙</span><span>🍜</span><span>🧊</span><span>☕</span><span>🍱</span><span>🍫</span><span>🥟</span>
    </div>
    <div class="relative z-10 h-full flex flex-col items-center justify-center text-center px-6">
        <h1 class="text-[28px] leading-snug font-bold text-ink tracking-tight">CS24에 오신 것을 환영합니다</h1>
        <p class="text-ink/70 mt-3 font-medium">매장을 선택해 상품을 담고 주문하면 픽업 코드를 받습니다.</p>
        <div class="mt-6 flex justify-center gap-3">
            <a href="login.php" class="inline-flex items-center justify-center h-12 px-6 rounded-lg bg-rausch hover:bg-rausch-active text-white font-medium transition-colors">로그인</a>
            <a href="signup.php" class="inline-flex items-center justify-center h-12 px-6 rounded-lg bg-canvas text-ink border border-ink hover:bg-surface-soft font-medium transition-colors">회원가입</a>
        </div>
    </div>
</div>

<div class="grid sm:grid-cols-2 gap-4 mt-10 max-w-4xl mx-auto">
    <a href="login.php" class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-3xl">🧊</div>
        <h3 class="text-base font-semibold text-ink mt-3">나만의 냉장고</h3>
        <p class="text-sm text-muted mt-1 leading-relaxed">구매한 증정품을 안전하게 보관하고 필요할 때 꺼내 드세요.</p>
    </a>
    <a href="login.php" class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-3xl">🔍</div>
        <h3 class="text-base font-semibold text-ink mt-3">실시간 재고 찾기</h3>
        <p class="text-sm text-muted mt-1 leading-relaxed">우리 동네 CS24 매장의 상품 재고를 실시간으로 확인합니다.</p>
    </a>
    <a href="login.php" class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-3xl">📦</div>
        <h3 class="text-base font-semibold text-ink mt-3">주문 &amp; 픽업 내역</h3>
        <p class="text-sm text-muted mt-1 leading-relaxed">내가 주문한 상품의 픽업 코드와 과거 내역을 조회합니다.</p>
    </a>
    <a href="login.php" class="card-hover block bg-canvas border border-hairline rounded-card p-6">
        <div class="text-3xl">🛵</div>
        <h3 class="text-base font-semibold text-ink mt-3">배달</h3>
        <p class="text-sm text-muted mt-1 leading-relaxed">편의점 상품을 집 앞까지 빠르게 배달해 드립니다.</p>
    </a>
</div>
<?php require 'footer.php'; ?>
