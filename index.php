<?php
session_start();
// 이미 로그인한 상태라면 회원용 메인 페이지로 자동 이동
if(isset($_SESSION['user_id'])) {
    header("Location: main.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>🏪 CS24 스마트 편의점</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 0; background: #f4f4f9; text-align: center; }
        header { background: #1e3a8a; color: white; padding: 30px; }
        .login-link { position: absolute; right: 20px; top: 35px; background: #f59e0b; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .container { max-width: 800px; margin: 50px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 30px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: pointer; transition: 0.2s; }
        .card:hover { transform: translateY(-5px); background: #f0f4ff; }
        .card h3 { color: #1e3a8a; margin-top: 0; }
        .card p { color: #666; font-size: 14px; }
    </style>
    <script>
        function requireLogin(featureName) {
            alert('🔒 ' + featureName + ' 기능은 로그인이 필요합니다. 로그인 화면으로 이동합니다.');
            window.location.href = 'login.php';
        }
    </script>
</head>
<body>

<header>
    <h1>🏪 CS24 스마트 편의점 플랫폼</h1>
    <a href="login.php" class="login-link">로그인 / 회원가입</a>
</header>

<div class="container">
    <h2>CS24에 오신 것을 환영합니다! 👋</h2>
    <p>원하시는 서비스를 선택해 주세요. (로그인 후 이용 가능)</p>
    
    <div class="grid">
        <div class="card" onclick="requireLogin('나만의 냉장고')">
            <h3>🧊 나만의 냉장고</h3>
            <p>구매한 증정품을 안전하게 보관하고 필요할 때 꺼내 드세요.</p>
        </div>
        <div class="card" onclick="requireLogin('실시간 재고 찾기')">
            <h3>🔍 실시간 재고 찾기</h3>
            <p>우리 동네 CS24 매장의 상품 재고를 실시간으로 확인합니다.</p>
        </div>
        <div class="card" onclick="requireLogin('스마트 배달')">
            <h3>🛵 스마트 배달 서비스</h3>
            <p>편의점 상품을 집 앞까지 신속하게 배달해 드립니다.</p>
        </div>
        <div class="card" onclick="requireLogin('주문/픽업 내역')">
            <h3>📦 주문 & 픽업 내역</h3>
            <p>내가 주문한 상품의 픽업 코드와 과거 내역을 조회합니다.</p>
        </div>
    </div>
</div>

</body>
</html>