<?php
include 'db_connect.php';

$sql = "TRUNCATE TABLE books";

if ($conn->query($sql) === TRUE) {
    echo "<script>
            alert('All books deleted succusfully');
            window.location='view_books.php';
          </script>";
} else {
    echo "❌ Error Whaile delete" . $conn->error;
}
?>
