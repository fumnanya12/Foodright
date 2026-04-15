<?php
session_start();
$_SESSION['logout']="Logged out successfully";
$redirect = $_SESSION['location']?? 'index.php';
session_destroy();
header("Location: $redirect");
exit();
?>