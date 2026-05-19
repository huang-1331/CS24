<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>CS24 - 회원가입</title>
    <style>
        body { font-family: sans-serif; background: #f4f4f9; display: flex; justify-content: center; padding-top: 50px; }
        .signup-box { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); width: 350px; }
        input { width: 95%; padding: 10px; margin: 8px 0; border: 1px solid #ccc; border-radius: 5px; box-sizing: border-box; }
        button { width: 100%; padding: 10px; background: #1e3a8a; color: white; border: none; border-radius: 5px; cursor: pointer; margin-top: 15px; font-weight: bold; font-size: 16px; }
        .back-link { text-align: center; margin-top: 15px; font-size: 14px; }
        .back-link a { color: #1e3a8a; text-decoration: none; }
    </style>
</head>
<body>
    <div class="signup-box">
        <h2>🏪 CS24 회원가입</h2>
        <p>간편하게 가입하고 스마트 편의점을 이용해 보세요.</p>
        <hr>
        <form action="signup_process.php" method="POST">
            <label><b>아이디</b></label>
            <input type="text" name="user_id" placeholder="사용할 아이디 입력" required>
            
            <label><b>비밀번호</b></label>
            <input type="password" name="user_pw" placeholder="비밀번호 입력" required>
            
            <label><b>이름</b></label>
            <input type="text" name="user_name" placeholder="홍길동" required>
            
            <label><b>전화번호</b></label>
            <input type="text" name="user_phone" placeholder="010-1234-5678" required>
            
            <button type="submit">가입하기</button>
        </form>
        <div class="back-link">
            <a href="login.php">이미 계정이 있으신가요? 로그인으로 이동</a>
        </div>
    </div>
</body>
</html>