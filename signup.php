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
<div class="max-w-md mx-auto bg-white rounded-lg shadow p-8">
    <h2 class="text-2xl font-bold text-blue-900 text-center">🏪 회원가입</h2>
    <p class="text-slate-500 text-sm text-center mt-1">간편하게 가입하고 CS24를 이용해 보세요.</p>

    <?php if ($errorMessage): ?>
        <div class="mt-4 bg-red-100 text-red-700 text-sm rounded px-4 py-2"><?= h($errorMessage) ?></div>
    <?php endif; ?>

    <form action="signup_process.php" method="POST" class="mt-6 space-y-3">
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">아이디</label>
            <input type="text" name="user_id" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">비밀번호</label>
            <input type="password" name="user_pw" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">이름</label>
            <input type="text" name="user_name" placeholder="홍길동" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <div>
            <label class="block text-sm font-semibold text-slate-700 mb-1">전화번호</label>
            <input type="text" name="user_phone" placeholder="010-1234-5678" required
                   class="w-full border border-slate-300 rounded px-3 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
        </div>
        <button type="submit"
                class="w-full bg-blue-900 hover:bg-blue-800 text-white font-semibold py-2 rounded mt-2">가입하기</button>
    </form>
    <p class="text-center text-sm text-slate-500 mt-4">
        이미 계정이 있으신가요? <a href="login.php" class="text-blue-700 font-semibold">로그인</a>
    </p>
</div>
<?php require 'footer.php'; ?>
