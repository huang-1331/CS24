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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    // CS24 Design Tokens (Airbnb-inspired)
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    rausch: '#ff385c',
                    'rausch-active': '#e00b41',
                    'rausch-disabled': '#ffd1da',
                    ink: '#222222',
                    body: '#3f3f3f',
                    muted: '#6a6a6a',
                    'muted-soft': '#929292',
                    hairline: '#dddddd',
                    'hairline-soft': '#ebebeb',
                    'border-strong': '#c1c1c1',
                    'surface-soft': '#f7f7f7',
                    'surface-strong': '#f2f2f2',
                    canvas: '#ffffff',
                    error: '#c13515',
                    'error-hover': '#b32505',
                },
                fontFamily: {
                    sans: ['Inter', '-apple-system', 'system-ui', '"Helvetica Neue"', 'Helvetica', 'Arial', 'sans-serif'],
                },
                boxShadow: {
                    card: 'rgba(0,0,0,0.02) 0 0 0 1px, rgba(0,0,0,0.04) 0 2px 6px 0, rgba(0,0,0,0.1) 0 4px 8px 0',
                },
                borderRadius: {
                    card: '14px',
                },
            },
        },
    };
    </script>
    <style>
    html, body { font-family: 'Inter', -apple-system, system-ui, 'Helvetica Neue', Helvetica, Arial, sans-serif; background: #ffffff; color: #222222; }
    h1, h2, h3 { color: #222222; letter-spacing: -0.018em; }
    h1 { font-weight: 700; }
    h2, h3 { font-weight: 600; }
    /* 8px / 14px / pill 라운드만 사용. focus 링 글로우 없음 */
    input[type="text"]:focus, input[type="password"]:focus, input[type="number"]:focus, input[type="email"]:focus, input[type="tel"]:focus, input[type="search"]:focus, textarea:focus {
        outline: none;
        border-color: #222222 !important;
        box-shadow: inset 0 0 0 1px #222222;
    }
    /* 카드 hover elevation - 단일 그림자 tier */
    .card-hover { transition: box-shadow 150ms ease, transform 150ms ease; }
    .card-hover:hover { box-shadow: rgba(0,0,0,0.02) 0 0 0 1px, rgba(0,0,0,0.04) 0 2px 6px 0, rgba(0,0,0,0.1) 0 4px 8px 0; }
    </style>
</head>
<body class="bg-canvas min-h-screen flex flex-col">
<nav class="bg-canvas border-b border-hairline">
    <div class="max-w-6xl mx-auto px-6 h-20 flex items-center justify-between">
        <a href="<?= $loggedIn ? 'main.php' : 'index.php' ?>" class="text-2xl font-bold text-rausch tracking-tight">CS24</a>
        <div class="flex items-center gap-6 text-base">
            <?php if ($loggedIn): ?>
                <a href="stores.php" class="text-ink font-semibold hover:underline underline-offset-4">매장</a>
                <a href="fridge.php" class="text-ink font-semibold hover:underline underline-offset-4">냉장고</a>
                <a href="orders.php" class="text-ink font-semibold hover:underline underline-offset-4">주문내역</a>
                <span class="text-muted text-sm hidden sm:inline"><?= h($_SESSION['user_name']) ?>님</span>
                <a href="logout.php" class="inline-flex items-center justify-center h-10 px-4 rounded-lg border border-ink text-ink font-medium hover:bg-surface-soft transition-colors">로그아웃</a>
            <?php else: ?>
                <a href="login.php" class="inline-flex items-center justify-center h-10 px-5 rounded-lg bg-rausch hover:bg-rausch-active text-white font-medium transition-colors">로그인 / 회원가입</a>
            <?php endif; ?>
        </div>
    </div>
</nav>
<main class="max-w-6xl w-full mx-auto px-6 py-12 flex-grow">
