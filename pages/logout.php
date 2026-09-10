<?php
// pages/logout.php
require_once '../config/session.php';

logout();
header('Location: ' . APP_URL . 'pages/login.php?logout=1');
exit();
?>