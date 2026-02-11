<?php
$servername = "localhost";  
$username   = "root";       
$password   = "";             
$dbname     = "library_system";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed!! " . $conn->connect_error);
}

// تعيين الترميز UTF-8 لدعم اللغة العربية
$conn->set_charset("utf8");
?>
