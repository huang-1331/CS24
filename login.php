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
<div class="max-w-sm mx-auto bg-white rounded-lg shadow p-8">
    <h2 class="text-2xl font-bold text-blue-900 text-center">🏪 로그인</h2>
    <p class="text-slate-500 text-sm text-center mt-1">서비스 이용을 위해 로그인해 주세요.</p>

    <?php if ($noticeMessage): ?>
        <div class="mt-4 bg-green-100 text-green-700 text-sm rounded px-4 py-2"><?= h($noticeMessage) ?></div>
    <?php endif; ?>
    <?php if ($errorMessage): ?>
        <div class="mt-4 bg-red-100 text-red-700 text-sm rounded px-4 py-2"><?= h($errorMessage) ?></div>
    <?php endif; ?>

    <form action="login_process.php" method="POST" class="mt-6 space-y-3">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">아이디</label>
            <input type="text" name="login_id" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">비밀번호</label>
            <input type="password" name="login_pw" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit"
                class="w-full bg-amber-500 hover:bg-amber-600 text-white font-semibold py-2 rounded mt-2">로그인</button>
    </form>
    <p class="text-center text-sm text-slate-500 mt-4">
        계정이 없으신가요? <a href="signup.php" class="text-blue-700 font-semibold">회원가입</a>
    </p>
</div>
<?php require 'footer.php'; ?>
