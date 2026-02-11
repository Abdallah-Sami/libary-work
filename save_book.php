<?php
include "db_connect.php";   

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $call_no      = $_POST['call_no'];
    $cutter_no    = $_POST['cutter_no'];
    $accession_no = $_POST['accession_no'];
    $campus       = $_POST['campus'];

    $stmt = $conn->prepare("INSERT INTO books (call_no, cutter_no, accession_no, campus) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $call_no, $cutter_no, $accession_no, $campus);
    $stmt->execute();
    $stmt->close();

    header("Location: view_books.php");   
    exit();
}
?>
<?php include 'header.php'; ?>
