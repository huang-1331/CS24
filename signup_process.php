<?php
// signup_process.php - 회원가입 처리 (HTML 출력 없이 redirect)
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: signup.php');
    exit();
}

$loginId = trim($_POST['user_id'] ?? '');
$password = $_POST['user_pw'] ?? '';
$name     = trim($_POST['user_name'] ?? '');
$phone    = trim($_POST['user_phone'] ?? '');

if ($loginId === '' || $password === '' || $name === '' || $phone === '') {
    header('Location: signup.php?error=empty');
    exit();
}

$hashedPw = password_hash($password, PASSWORD_DEFAULT);

try {
    // prepared statement 로 SQL 인젝션을 차단한다.
    $stmt = $conn->prepare(
        "INSERT INTO P_USER (userLoginId, userPassword, userName, userPhoneNumber)
         VALUES (?, ?, ?, ?)"
    );
    $stmt->bind_param("ssss", $loginId, $hashedPw, $name, $phone);
    $stmt->execute();
    $stmt->close();

    header('Location: login.php?signup=success');
    exit();
} catch (mysqli_sql_exception $e) {
    // 아이디/전화번호 UNIQUE 충돌 등 - 상세 오류는 사용자에게 노출하지 않는다.
    header('Location: signup.php?error=duplicate');
    exit();
}
