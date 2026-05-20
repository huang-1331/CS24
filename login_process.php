<?php
// login_process.php - 로그인 처리 (HTML 출력 없이 redirect)
require 'db.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: login.php');
    exit();
}

$loginId  = trim($_POST['login_id'] ?? '');
$password = $_POST['login_pw'] ?? '';

// prepared statement 로 아이디를 조회한다.
$stmt = $conn->prepare(
    "SELECT userId, userName, userPassword
     FROM P_USER
     WHERE userLoginId = ? AND deletedAt IS NULL"
);
$stmt->bind_param("s", $loginId);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$stmt->close();

if ($user && password_verify($password, $user['userPassword'])) {
    $_SESSION['user_id']   = $user['userId'];
    $_SESSION['user_name'] = $user['userName'];
    header('Location: main.php');
    exit();
}

// 아이디 없음 / 비밀번호 불일치를 구분하지 않는다.
header('Location: login.php?error=invalid');
exit();
