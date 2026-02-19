<?php
include 'db_connect.php';

$sql = "CREATE TABLE IF NOT EXISTS coop_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    academic_id VARCHAR(100) NOT NULL,
    department VARCHAR(255),
    major VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    iban VARCHAR(100),
    bank_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)";

if ($conn->query($sql) === TRUE) {
    echo "✅ تم إنشاء جدول طلاب التدريب التعاوني بنجاح!";
} else {
    echo "❌ خطأ: " . $conn->error;
}
$conn->close();
?>
