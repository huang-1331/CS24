<?php
// header.php - 공통 상단 레이아웃
// 이 파일을 require 하기 전에 각 페이지에서 session_start() 와 db.php 를 먼저 불러야 한다.
$pageTitle = $pageTitle ?? 'CS24 편의점';
$loggedIn  = isset($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= h($pageTitle) ?> | CS24</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-100 min-h-screen flex flex-col">
<nav class="bg-blue-900 text-white shadow">
    <div class="max-w-5xl mx-auto px-4 py-3 flex items-center justify-between">
        <a href="<?= $loggedIn ? 'main.php' : 'index.php' ?>" class="text-xl font-bold">🏪 CS24</a>
        <div class="flex items-center gap-3 text-sm">
            <?php if ($loggedIn): ?>
                <a href="stores.php" class="hover:underline">매장</a>
                <a href="orders.php" class="hover:underline">주문내역</a>
                <span class="font-semibold">👤 <?= h($_SESSION['user_name']) ?>님</span>
                <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded">로그아웃</a>
            <?php else: ?>
                <a href="login.php" class="bg-amber-500 hover:bg-amber-600 px-3 py-1 rounded font-semibold">로그인 / 회원가입</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="max-w-5xl w-full mx-auto px-4 py-8 flex-grow">
