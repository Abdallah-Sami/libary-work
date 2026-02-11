<?php
error_reporting(0);
include 'auth.php';
logout();
header("Location: login.php");
exit();
?>
