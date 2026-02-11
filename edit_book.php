<?php
include 'db_connect.php';

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $sql = "SELECT * FROM books WHERE id = $id";
    $result = $conn->query($sql);

    if ($result->num_rows == 1) {
        $book = $result->fetch_assoc();
    } else {
        die("<script>alert('Book not found!'); window.location='view_books.php';</script>");
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $call_no = $conn->real_escape_string($_POST['call_no']);
    $cutter_no = $conn->real_escape_string($_POST['cutter_no']);
    $accession_no = $conn->real_escape_string($_POST['accession_no']);
    $campus = $conn->real_escape_string($_POST['campus']);

    $update_sql = "UPDATE books SET 
        call_no='$call_no',
        cutter_no='$cutter_no',
        accession_no='$accession_no',
        campus='$campus'
        WHERE id=$id";

    if ($conn->query($update_sql) === TRUE) {
        echo "<script>alert('✅ Book updated successfully!'); window.location='view_books.php';</script>";
    } else {
        echo "<script>alert('Error: " . $conn->error . "');</script>";
    }
}

include 'header.php';
?>

<div class="content">
    <h2 class="page-title">✏️ Edit Book</h2>

    <form method="POST" class="form-container">
        <div class="form-group">
            <label for="call_no">📞 Call Number</label>
            <input type="text" id="call_no" name="call_no" class="form-control" value="<?= htmlspecialchars($book['call_no']) ?>" required>
        </div>

        <div class="form-group">
            <label for="cutter_no">✂️ Cutter Number</label>
            <input type="text" id="cutter_no" name="cutter_no" class="form-control" value="<?= htmlspecialchars($book['cutter_no']) ?>" required>
        </div>

        <div class="form-group">
            <label for="accession_no">🔢 Accession Number</label>
            <input type="text" id="accession_no" name="accession_no" class="form-control" value="<?= htmlspecialchars($book['accession_no']) ?>" required>
        </div>

        <div class="form-group">
            <label for="campus">🏫 Campus</label>
            <input type="text" id="campus" name="campus" class="form-control" value="<?= htmlspecialchars($book['campus']) ?>" required>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-primary">💾 Save Changes</button>
            <a href="view_books.php" class="btn btn-secondary">⬅ Back to List</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
