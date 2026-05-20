<?php
// logout.php - 세션 종료 후 홈으로 이동
session_start();
session_destroy();
header('Location: index.php');
exit();
