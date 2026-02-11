<?php include 'header.php'; ?>

<div class="content">
    <h2 class="page-title">➕ Add New Book</h2>

    <form action="save_book.php" method="POST" class="form-container">
        <div class="form-group">
            <label for="call_no">📞 Call Number</label>
            <input type="text" id="call_no" name="call_no" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="cutter_no">✂️ Cutter Number</label>
            <input type="text" id="cutter_no" name="cutter_no" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="accession_no">🔢 Accession Number</label>
            <input type="text" id="accession_no" name="accession_no" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="campus">🏫 Campus</label>
            <input type="text" id="campus" name="campus" class="form-control" required>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-primary">💾 Save Book</button>
            <a href="index.php" class="btn btn-secondary">⬅ Back to Home</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>
