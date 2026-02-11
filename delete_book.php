<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    $sql = "DELETE FROM books WHERE id = $id";
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>
                alert('✅ Book deleted successfully!');
                window.location='view_books.php';
              </script>";
    } else {
        echo "<script>
                alert('❌ Error deleting book: " . $conn->error . "');
                window.location='view_books.php';
              </script>";
    }
} else {
    header("Location: view_books.php");
    exit();
}
?>
