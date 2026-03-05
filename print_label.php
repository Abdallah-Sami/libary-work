<?php
include 'db_connect.php';
include 'header.php';
$sql = "SELECT * FROM books ORDER BY id ASC LIMIT 40";
$result = $conn->query($sql);
?>
<script src="https://cdn.jsdelivr.net/npm/jsbarcode@3.11.5/dist/JsBarcode.all.min.js"></script>

<style>
/* ===== LABEL GRID ===== */
.label-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 10px;
    max-width: 210mm;
    margin: 0 auto;
}

.label {
    border: 1px solid #333;
    padding: 10px;
    text-align: center;
    height: 55mm;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
    align-items: center;
    background: #fff;
}

.label-logo {
    max-width: 50px;
    margin-bottom: 3px;
}

.label-title {
    font-weight: bold;
    font-size: 12px;
    margin-bottom: 5px;
}

.label-info {
    font-size: 11px;
    line-height: 1.4;
    margin-bottom: 5px;
}

.barcode-wrapper {
    margin: 5px 0;
}

.barcode {
    display: block;
    margin: 0 auto;
}

.label-footer {
    font-size: 9px;
    margin-top: auto;
    color: #333;
}

/* ===== PRINT STYLES ===== */
@media print {
    .no-print {
        display: none !important;
    }
    
    body {
        margin: 0;
        padding: 0;
        background: #fff;
    }
    
    .content {
        padding: 0;
        margin: 0;
    }
    
    .label-grid {
        gap: 5px;
    }
    
    .label {
        page-break-inside: avoid;
        border: 1px solid #000;
    }
}
</style>

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
$barcodes = [];
echo '<div class="label-grid">';

while($row = $result->fetch_assoc()) {
    $id = $row['id'];
    $acc = htmlspecialchars($row['accession_no']);
    $call = htmlspecialchars($row['call_no']);
    $cutter = htmlspecialchars($row['cutter_no']);
    
    $barcodes[] = ['id' => $id, 'acc' => $acc];
    
    echo "<div class='label'>
            <img src='college.png' alt='Library Logo' class='label-logo'>
            <div class='label-title'>YIC-M Library</div>
            <div class='label-info'>
                $call<br>
                $cutter<br>
                $acc
            </div>
            <div class='barcode-wrapper'>
                <svg class='barcode' id='barcode-$id'></svg>
            </div>
            <div class='label-footer'>
                Return on time<br>
                library.rcjy.edu.sa
            </div>
          </div>";
    
    $counter++;
    if ($counter % 10 == 0 && $counter < $result->num_rows) {
        echo '</div><div class="label-grid">';
    }
}
echo '</div>';
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    <?php foreach($barcodes as $b): ?>
    JsBarcode('#barcode-<?= $b['id'] ?>', '<?= $b['acc'] ?>', {
        format: 'CODE128',
        displayValue: true,
        fontSize: 10,
        width: 1.5,
        height: 15,
        margin: 0,
        background: 'transparent'
    });
    <?php endforeach; ?>
});
</script>

</div>
<?php include 'footer.php'; ?>