<?php
include 'db_connect.php';

if ($conn->query("TRUNCATE TABLE books") === TRUE) {
    echo "<script>alert('All books deleted successfully'); window.location='view_books.php';</script>";
} else {
    echo "<script>alert('Error while deleting: " . $conn->error . "'); window.location='view_books.php';</script>";
}
?>
