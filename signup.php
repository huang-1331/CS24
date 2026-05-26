<?php
require 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: main.php');
    exit();
}

$error = $_GET['error'] ?? '';
$errorMessage = '';
if ($error === 'empty') {
    $errorMessage = '모든 항목을 입력해 주세요.';
} elseif ($error === 'duplicate') {
    $errorMessage = '이미 사용 중인 아이디 또는 전화번호입니다.';
}

$pageTitle = '회원가입';
require 'header.php';
?>
<div class="max-w-md mx-auto bg-canvas border border-hairline rounded-card p-8">
    <h2 class="text-[22px] font-semibold text-ink text-center tracking-tight">회원가입</h2>
    <p class="text-muted text-sm text-center mt-2">간편하게 가입하고 CS24를 이용해 보세요.</p>

    <?php if ($errorMessage): ?>
        <div class="mt-5 text-error text-sm font-medium"><?= h($errorMessage) ?></div>
    <?php endif; ?>

    <form action="signup_process.php" method="POST" class="mt-6 space-y-4">
        <div>
            <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">아이디</label>
            <input type="text" name="user_id" required
                   class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
        </div>
        <div>
            <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">비밀번호</label>
            <input type="password" name="user_pw" required
                   class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
        </div>
        <div>
            <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">이름</label>
            <input type="text" name="user_name" placeholder="홍길동" required
                   class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
        </div>
        <div>
            <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">전화번호</label>
            <input type="text" name="user_phone" placeholder="010-1234-5678" required
                   class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
        </div>
        <button type="submit"
                class="w-full h-12 bg-rausch hover:bg-rausch-active text-white font-medium rounded-lg mt-2 transition-colors">가입하기</button>
    </form>
    <p class="text-center text-sm text-muted mt-6">
        이미 계정이 있으신가요? <a href="login.php" class="text-ink font-semibold underline underline-offset-4">로그인</a>
    </p>
</div>
<?php require 'footer.php'; ?>
