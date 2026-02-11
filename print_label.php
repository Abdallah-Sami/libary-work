<?php
include 'db_connect.php';
include 'header.php';

$sql = "SELECT * FROM books ORDER BY id ASC LIMIT 40";
$result = $conn->query($sql);
?>

<div class="content">
    <h2 class="page-title">🏷️ Print Book Labels</h2>

    <div class="action-buttons no-print">
        <button onclick="window.print()" class="btn btn-success">🖨️ Print Labels</button>
        <a href="view_books.php" class="btn btn-secondary">⬅ Back to List</a>
        <a href="index.php" class="btn btn-info">🏠 Home</a>
    </div>

    <div style="margin: 20px 0; padding: 15px; background: #fff3cd; border-radius: 8px;" class="no-print">
        <strong>📋 Print Instructions:</strong>
        <ul style="margin: 10px 0 0 20px;">
            <li>This page prints 10 labels per sheet (2 columns × 5 rows)</li>
            <li>Use A4 paper in portrait orientation</li>
            <li>Adjust printer settings for best quality</li>
            <li>Labels include barcode and library information</li>
        </ul>
    </div>

<?php
$counter = 0;
echo '<div class="label-grid">';

while($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $acc = htmlspecialchars($row['accession_no']);
    $call = htmlspecialchars($row['call_no']);
    $cutter = htmlspecialchars($row['cutter_no']);
    
    echo "<div class='label'>
            <div>
                <img src='college.png' alt='Library Logo' style='max-width: 50px; margin: 5px auto;'>
                <strong>YIC-M Library</strong>
            </div>
            <div>
                Call No: $call<br>
                Cutter: $cutter<br>
                Acc: $acc
            </div>
            <svg class='barcode' id='barcode-$id'></svg>
            <div style='font-size: 9px;'>
                Return on time<br>
                library.rcjy.edu.sa
            </div>
          </div>";
    
    echo "<script>
            JsBarcode('#barcode-$id', '$acc', {
                format: 'CODE128',
                displayValue: true,
                fontSize: 10,
                width: 1.5,
                height: 35
            });
          </script>";
    
    $counter++;
    if ($counter % 10 == 0 && $counter < $result->num_rows) {
        echo '</div><div class="label-grid">';
    }
}

echo '</div>';
?>

</div>

<!-- Barcode library -->
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<?php include 'footer.php'; ?>
