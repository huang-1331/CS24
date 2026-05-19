<?php
// logout.php
session_start();
session_destroy(); // 모든 세션 정보 삭제 (도장 파기)

echo "<script>alert('정상적으로 로그아웃 되었습니다.'); window.location.href='index.php';</script>";
?>