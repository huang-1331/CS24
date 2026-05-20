<?php
// db.php - 데이터베이스 연결 (모든 페이지에서 require)

// mysqli 오류를 예외로 발생시켜 prepare/execute 실패를 try-catch로 처리할 수 있게 한다.
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host   = "localhost";
$user   = "db26ykcho";
$pass   = "2023103961";
$dbName = "db26ykcho";

$conn = new mysqli($host, $user, $pass, $dbName);
$conn->set_charset("utf8mb4");

// 출력 시 XSS 방지용 이스케이프 헬퍼
function h($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}
