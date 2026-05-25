<?php
session_start();

// 로그인하지 않은 사용자가 강제로 주소를 쳐서 들어오는 것을 방지 (보안)
if(!isset($_SESSION['user_id'])) {
    echo "<script>alert('비정상적인 접근입니다. 로그인을 먼저 해주세요.'); window.location.href='login.php';</script>";
    exit();
}

$user_name = $_SESSION['user_name'];
?>
<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>CS24 - 메인 홈</title>
    <style>
        body { font-family: sans-serif; margin: 0; background: #f4f4f9; text-align: center; }
        header { background: #1e3a8a; color: white; padding: 20px; display: flex; justify-content: space-between; align-items: center; }
        .user-info { font-size: 18px; font-weight: bold; }
        .logout-btn { background: #ef4444; color: white; padding: 8px 15px; border-radius: 5px; text-decoration: none; font-weight: bold; }
        .container { max-width: 800px; margin: 40px auto; padding: 0 20px; }
        .grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-top: 30px; }
        .card { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); cursor: pointer; text-decoration: none; color: inherit; display: block; transition: 0.2s; }
        .card:hover { transform: translateY(-5px); background: #f0f4ff; border: 2px solid #1e3a8a; }
        .card h3 { color: #1e3a8a; margin-top: 0; }
    </style>
</head>
<body>

<header>
    <div class="user-info">🏪 CS24 | 👤 <?php echo $user_name; ?>님 환영합니다!</div>
    <a href="logout.php" class="logout-btn">로그아웃</a>
</header>

<div class="container">
    <h2>무엇을 도와드릴까요?</h2>
    
    <div class="grid">
        <a href="fridge.php" class="card">
            <h3>🧊 나만의 냉장고</h3>
            <p>보관 중인 증정품 확인 및 꺼내기</p>
        </a>
        <a href="inventory.php" class="card">
            <h3>🔍 실시간 재고 찾기</h3>
            <p>우리 동네 매장 상품 재고 조회</p>
        </a>
        <a href="delivery.php" class="card">
            <h3>🛵 스마트 배달</h3>
            <p>상품 배달 주문하기</p>
        </a>
        <a href="history.php" class="card">
            <h3>📦 주문 & 픽업 내역</h3>
            <p>나의 과거 주문 및 픽업 코드 확인</p>
        </a>
    </div>
</div>

</body>
</html>