<?php
$servername = "sql209.infinityfree.com";
$username = "if0_41099273";
$password = "MUwsGL5tofUrqdY";
$dbname = "if0_41099273_library_system";

// إنشاء الاتصال
$conn = new mysqli($servername, $username, $password, $dbname);

// التحقق من الاتصال
if ($conn->connect_error) {
    die("Connection failed!! " . $conn->connect_error);
}

// تعيين الترميز UTF-8 لدعم اللغة العربية
$conn->set_charset("utf8");
?>
