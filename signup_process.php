<?php
// signup_process.php
include "db.php"; 

$user_id = $_POST['user_id'];
$user_pw = $_POST['user_pw'];
$user_name = $_POST['user_name'];
$user_phone = $_POST['user_phone'];

// 비밀번호 안전하게 암호화
$hashed_pw = password_hash($user_pw, PASSWORD_DEFAULT);

// 💡 Unknown column 에러를 피하기 위해 필수 컬럼 4개만 정확히 찌릅니다.
$sql = "INSERT INTO P_USER (userLoginId, userPassword, userName, userPhoneNumber) 
        VALUES ('$user_id', '$hashed_pw', '$user_name', '$user_phone')";

if (mysqli_query($conn, $sql)) {
    echo "<script>alert('🎉 CS24 회원가입 성공! 로그인 창으로 이동합니다.'); window.location.href='login.php';</script>";
} else {
    echo "<script>alert('❌ 가입 실패: " . mysqli_error($conn) . "'); history.back();</script>";
}
?>