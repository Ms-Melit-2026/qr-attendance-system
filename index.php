# index.php
<?php
// Main entry point - redirect to login
require_once 'config/constants.php';
header('Location: ' . APP_URL . 'pages/login.php');
exit();
?>