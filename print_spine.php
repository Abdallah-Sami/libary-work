<?php
include 'db_connect.php';
include 'header.php';

$sql = "SELECT * FROM books ORDER BY id ASC LIMIT 40";
$result = $conn->query($sql);
?>

<style>
body {
    background: white;
}

.spine-container {
    display: grid;
    grid-template-columns: repeat(4, 1fr); /* 4 spines per row */
    gap: 15px;
    padding: 20px;
    max-width: 100%;
}

.spine-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    height: 180px; /* Space for rotated spine */
    position: relative;
}

.spine {
    border: 2px solid #000;
    width: 150px;
    height: 60px;
    font-size: 13px;
    padding: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    line-height: 1.4;
    font-weight: 600;
    text-align: center;
    transform: rotate(90deg);
    transform-origin: center center;
    background: white;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

@media print {
    body {
        background: white;
        margin: 0;
        padding: 0;
    }
    
    .no-print {
        display: none !important;
    }
    
    .container {
        box-shadow: none;
        border-radius: 0;
        max-width: 100%;
    }
    
    .main-header {
        display: none !important;
    }
    
    .content {
        padding: 0;
        margin: 0;
    }
    
    @page {
        size: A4 landscape;
        margin: 10mm;
    }
    
    .spine-container {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        padding: 5mm;
        page-break-inside: avoid;
    }
    
    .spine-wrapper {
        height: 180px;
        page-break-inside: avoid;
    }
    
    .spine {
        border: 2px solid #000 !important;
        box-shadow: none;
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        page-break-inside: avoid;
    }
}

@media screen {
    .page-title {
        text-align: center;
        margin: 20px 0;
    }
}
</style>

<div class="content">
    <h2 class="page-title no-print">📖 Print Book Spines</h2>

    <div class="action-buttons no-print">
        <button onclick="window.print()" class="btn btn-success">🖨️ Print Spines</button>
        <a href="view_books.php" class="btn btn-secondary">⬅ Back to List</a>
        <a href="index.php" class="btn btn-info">🏠 Home</a>
    </div>

    <div style="margin: 20px; padding: 15px; background: #d1ecf1; border-radius: 8px;" class="no-print">
        <strong>📋 Print Instructions:</strong>
        <ul style="margin: 10px 0 0 20px;">
            <li>Layout: 4 spines per row, multiple rows per page</li>
            <li>Spines are rotated 90° automatically</li>
            <li>Use A4 paper in <strong>LANDSCAPE</strong> orientation</li>
            <li>All borders will be visible for cutting</li>
            <li>Cut along the black borders after printing</li>
        </ul>
    </div>

    <div class="spine-container">
    <?php while($row = $result->fetch_assoc()): ?>
        <div class="spine-wrapper">
            <div class="spine">
                <div>
                    YICM<br>
                    <?= htmlspecialchars($row['call_no']) ?><br>
                    <?= htmlspecialchars($row['cutter_no']) ?>
                </div>
            </div>
        </div>
    <?php endwhile; ?>
    </div>
</div>

<?php include 'footer.php'; ?>

