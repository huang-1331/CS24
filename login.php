<?php
require 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: main.php');
    exit();
}

$errorMessage = ($_GET['error'] ?? '') === 'invalid' ? '아이디 또는 비밀번호가 올바르지 않습니다.' : '';
$noticeMessage = ($_GET['signup'] ?? '') === 'success' ? '회원가입이 완료되었습니다. 로그인해 주세요.' : '';

$pageTitle = '로그인';
require 'header.php';
?>
<div class="max-w-md mx-auto bg-canvas border border-hairline rounded-card p-8">
    <h2 class="text-[22px] font-semibold text-ink text-center tracking-tight">로그인</h2>
    <p class="text-muted text-sm text-center mt-2">서비스 이용을 위해 로그인해 주세요.</p>
    <p class="text-muted-soft text-xs text-center mt-1">※ 테스트 계정: test / 1234</p>

    <?php if ($noticeMessage): ?>
        <div class="mt-5 bg-surface-soft text-ink text-sm rounded-lg px-4 py-3"><?= h($noticeMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="mt-5 text-error text-sm font-medium"><?= h($errorMessage) ?></div>
    <?php endif; ?>

    <form action="login_process.php" method="POST" class="mt-6 space-y-4">
        <div>
            <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">아이디</label>
            <input type="text" name="login_id" required
                   class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
        </div>
        <div>
            <label class="block text-xs font-medium text-muted mb-1.5 uppercase tracking-wide">비밀번호</label>
            <input type="password" name="login_pw" required
                   class="w-full h-14 border border-hairline rounded-lg px-4 text-ink placeholder-muted-soft">
        </div>
        <button type="submit"
                class="w-full h-12 bg-rausch hover:bg-rausch-active text-white font-medium rounded-lg mt-2 transition-colors">로그인</button>
    </form>
    <p class="text-center text-sm text-muted mt-6">
        계정이 없으신가요? <a href="signup.php" class="text-ink font-semibold underline underline-offset-4">회원가입</a>
    </p>
</div>
<?php require 'footer.php'; ?>
