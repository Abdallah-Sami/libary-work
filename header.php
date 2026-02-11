<?php
error_reporting(0);
include_once 'auth.php';
requireAuth();
$username = getUser();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css?v=999">
</head>
<body>

<div class="container">
    <header class="main-header no-print">
        <div class="brand-wrap">
            <img src="college.png" alt="شعار الهيئة الملكية" class="site-logo">
            <div>
                <h1>📚 Library</h1>
                <p class="brand-subtitle">Yanbu Industrial College - Main Library</p>
                <p class="brand-user">
                    👤 مرحباً: <?= htmlspecialchars($username) ?>
                </p>
            </div>
        </div>
        <nav class="main-nav">
            <a href="index.php" class="nav-btn">🏠 Home</a>
            <a href="add_book.php" class="nav-btn">➕ Add Book</a>
            <a href="view_books.php" class="nav-btn">📋 View Books</a>
            <a href="print_label.php" class="nav-btn">🏷️ Labels</a>
            <a href="print_spine.php" class="nav-btn">📖 Spines</a>
            <a href="manage_students.php" class="nav-btn">👥 Students</a>
            <a href="salary_sheet.php" class="nav-btn">💰 Salary</a>
            <a href="attendance_form.php" class="nav-btn">📝 Attendance</a>
            <a href="logout.php" class="nav-btn" style="background: rgba(220, 53, 69, 0.8);" 
               onclick="return confirm('هل أنت متأكد من تسجيل الخروج؟')">🚪 خروج</a>
        </nav>
    </header>
