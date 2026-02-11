<?php
$servername = "sql209.infinityfree.com";
$username   = "if0_41099273";
$password   = "MUwsGL5tofUrqdY";
$dbname     = "if0_41099273_library_system";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
} else {
    echo "Connected successfully!";
}
?>