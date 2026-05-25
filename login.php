<!DOCTYPE html>
<html lang="ko">
<head>
    <meta charset="UTF-8">
    <title>CS24 - 로그인</title>
    <style>
        body { font-family: sans-serif; background: #1e3a8a; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
        .login-box { background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); width: 300px; text-align: center; }
        input { width: 90%; padding: 10px; margin: 10px 0; border: 1px solid #ccc; border-radius: 5px; }
        button { width: 100%; padding: 10px; background: #f59e0b; border: none; border-radius: 5px; font-weight: bold; cursor: pointer; color: white; font-size: 16px; margin-top: 10px; }
        .links { margin-top: 20px; font-size: 14px; display: flex; justify-content: space-between; }
        .links a { text-decoration: none; color: #1e3a8a; font-weight: bold; }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>🏪 CS24 로그인</h2>
        <p>서비스 이용을 위해 로그인해 주세요.<br>
            테스트 ID: test / PW: 1234
        </p>
        <hr>
        <form action="login_process.php" method="POST">
            <input type="text" name="login_id" placeholder="아이디" required>
            <input type="password" name="login_pw" placeholder="비밀번호" required>
            <button type="submit">로그인</button>
        </form>
        <div class="links">
            <a href="index.php">← 홈으로</a>
            <a href="signup.php">회원가입 하기 →</a>
        </div>
    </div>
</body>
</html>