<?php
// db.php
$host = "localhost";
$user = "db26ykcho";       
$pass = "2023103961";
$dbName = "db26ykcho";     

$conn = mysqli_connect($host, $user, $pass, $dbName);

if (!$conn) {
    die("DB 연결 실패: " . mysqli_connect_error());
}
mysqli_set_charset($conn, "utf8mb4");
?>