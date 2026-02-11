<?php
include 'db_connect.php';
include 'header.php';

$sql = "SELECT * FROM books ORDER BY id ASC";
$result = $conn->query($sql);
$total_books = $result->num_rows;
?>

<div class="content">
    <h2 class="page-title">📚 Books List</h2>

    <div class="stats-box">
        ✅ Total Books: <?= $total_books ?> / 40
    </div>

    <div class="action-buttons no-print">
        <a href="add_book.php" class="btn btn-primary">➕ Add New Book</a>
        <a href="print_label.php" class="btn btn-success">🏷️ Print Labels</a>
        <a href="print_spine.php" class="btn btn-info">📖 Print Spines</a>
        <a href="delete_all_books.php" class="btn btn-danger" onclick="return confirmDelete('Are you sure you want to delete ALL books? This cannot be undone!')">
            🗑️ Delete All Books
        </a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Call No</th>
                <th>Cutter No</th>
                <th>Accession No</th>
                <th>Campus</th>
                <th class="no-print">Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= $row['id'] ?></td>
                        <td><?= htmlspecialchars($row['call_no']) ?></td>
                        <td><?= htmlspecialchars($row['cutter_no']) ?></td>
                        <td><?= htmlspecialchars($row['accession_no']) ?></td>
                        <td><?= htmlspecialchars($row['campus']) ?></td>
                        <td class="no-print">
                            <a href="edit_book.php?id=<?= $row['id'] ?>" class="btn btn-warning" style="padding: 6px 12px; margin: 2px;">✏️ Edit</a>
                            <a href="delete_book.php?id=<?= $row['id'] ?>" class="btn btn-danger" style="padding: 6px 12px; margin: 2px;" 
                               onclick="return confirmDelete('Delete this book?')">🗑️ Delete</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr><td colspan="6" style="text-align: center; padding: 30px;">📚 No books added yet. Click "Add New Book" to start.</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php include 'footer.php'; ?>
