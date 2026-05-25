<?php
// login_process.php
session_start();
include "db.php";

$login_id = $_POST['login_id'];
$login_pw = $_POST['login_pw'];

// 데이터베이스에서 해당 아이디 검색
$sql = "SELECT * FROM P_USER WHERE userLoginId = '$login_id'";
$result = mysqli_query($conn, $sql);

if(mysqli_num_rows($result) > 0) {
    $row = mysqli_fetch_assoc($result);
    
    // 암호화된 비밀번호 대조 검증
    if(password_verify($login_pw, $row['userPassword'])) {
        // 로그인 성공 시 세션에 회원 정보 저장
        $_SESSION['user_id'] = $row['userId'];
        $_SESSION['user_name'] = $row['userName'];
        
        echo "<script>alert('🔑 " . $row['userName'] . "님, 반갑습니다!'); window.location.href='main.php';</script>";
    } else {
        echo "<script>alert('❌ 비밀번호가 올바르지 않습니다.'); history.back();</script>";
    }
} else {
    echo "<script>alert('❌ 존재하지 않는 아이디입니다.'); history.back();</script>";
}
?>