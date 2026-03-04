<?php
require_once 'db_connect.php';

$sheet_id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? 'view';

if ($sheet_id <= 0) {
    die("<script>alert('كشف غير صالح!'); window.location='saved_sheets.php';</script>");
}

// Get sheet data
$sheet_result = $conn->query("SELECT * FROM salary_sheets WHERE id = $sheet_id");
if (!$sheet_result || $sheet_result->num_rows == 0) {
    die("<script>alert('الكشف غير موجود!'); window.location='saved_sheets.php';</script>");
}
$sheet = $sheet_result->fetch_assoc();

// Get items separated by daily hours
$items_2h = $conn->query("SELECT * FROM salary_sheet_items WHERE sheet_id = $sheet_id AND (daily_hours = 2 OR daily_hours IS NULL) ORDER BY id ASC");
$items_3h = $conn->query("SELECT * FROM salary_sheet_items WHERE sheet_id = $sheet_id AND daily_hours = 3 ORDER BY id ASC");

$students_2h = [];
$students_3h = [];
$total_2h = 0;
$total_3h = 0;

if ($items_2h) {
    while ($row = $items_2h->fetch_assoc()) {
        $students_2h[] = $row;
        $total_2h += $row['amount'];
    }
}
if ($items_3h) {
    while ($row = $items_3h->fetch_assoc()) {
        $students_3h[] = $row;
        $total_3h += $row['amount'];
    }
}

$total_amount = $total_2h + $total_3h;
$period_from_formatted = date('d/m/Y', strtotime($sheet['period_from']));
$period_to_formatted = date('d/m/Y', strtotime($sheet['period_to']));

// Convert number to Arabic words
function numberToArabicWords($number) {
    $number = intval($number);
    $ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
    $tens = ['', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    $hundreds = ['', 'مئة', 'مئتان', 'ثلاثمئة', 'أربعمئة', 'خمسمئة', 'ستمئة', 'سبعمئة', 'ثمانمئة', 'تسعمئة'];
    $thousands = ['', 'ألف', 'ألفان', 'ثلاثة آلاف', 'أربعة آلاف', 'خمسة آلاف', 'ستة آلاف', 'سبعة آلاف', 'ثمانية آلاف', 'تسعة آلاف'];
    if ($number == 0) return 'صفر';
    $result = '';
    $thou = floor($number / 1000);
    if ($thou > 0) { $result .= $thousands[$thou] . ' '; $number = $number % 1000; }
    $hund = floor($number / 100);
    if ($hund > 0) { $result .= $hundreds[$hund] . ' و'; $number = $number % 100; }
    $ten = floor($number / 10);
    if ($ten > 1) { $result .= $tens[$ten] . ' و'; $number = $number % 10; }
    if ($number > 0) { $result .= $ones[$number]; }
    return trim($result, ' و') . ' ريال سعودي';
}

// Helper for Excel rows
function generateExcelRows($students, &$counter) {
    $output = '';
    foreach ($students as $s) {
        $output .= '<Row>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $counter++ . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['student_name']) . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['academic_id']) . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['phone']) . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $s['hours'] . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $s['hourly_rate'] . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $s['amount'] . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['iban']) . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['bank_name']) . '</Data></Cell>';
        $output .= '</Row>';
    }
    return $output;
}

// ===== EXCEL EXPORT =====
if ($action == 'excel') {
    $filename = 'salary_sheet_' . $sheet['period_from'] . '_' . $sheet['period_to'] . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
    echo '<Styles>';
    echo '<Style ss:ID="header"><Font ss:Bold="1" ss:Size="14"/><Alignment ss:Horizontal="Center"/></Style>';
    echo '<Style ss:ID="subheader"><Alignment ss:Horizontal="Center"/></Style>';
    echo '<Style ss:ID="sectionTitle"><Font ss:Bold="1" ss:Size="12" ss:Color="#1B2D6B"/><Alignment ss:Horizontal="Center"/><Interior ss:Color="#E8F5E9" ss:Pattern="Solid"/></Style>';
    echo '<Style ss:ID="tableHeader"><Font ss:Bold="1"/><Alignment ss:Horizontal="Center"/><Interior ss:Color="#CCCCCC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
    echo '<Style ss:ID="tableCell"><Alignment ss:Horizontal="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
    echo '<Style ss:ID="totalRow"><Font ss:Bold="1"/><Interior ss:Color="#FFFF00" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
    echo '<Style ss:ID="subtotalRow"><Font ss:Bold="1"/><Interior ss:Color="#E8F5E9" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
    echo '<Style ss:ID="footerLabel"><Font ss:Bold="1"/></Style>';
    echo '</Styles>';
    echo '<Worksheet ss:Name="كشف الحساب" ss:RightToLeft="1"><Table>';
    echo '<Column ss:Width="40"/><Column ss:Width="150"/><Column ss:Width="100"/><Column ss:Width="100"/><Column ss:Width="80"/><Column ss:Width="80"/><Column ss:Width="80"/><Column ss:Width="200"/><Column ss:Width="100"/>';

    echo '<Row ss:Height="25"><Cell ss:MergeAcross="8" ss:StyleID="header"><Data ss:Type="String">قائم تشغيل نادي</Data></Cell></Row>';
    echo '<Row><Cell ss:MergeAcross="8" ss:StyleID="subheader"><Data ss:Type="String">خلال الفترة من ' . $period_from_formatted . ' إلى ' . $period_to_formatted . ' م</Data></Cell></Row>';
    echo '<Row/>';

    $headers = ['م', 'الاسم', 'الرقم الاكاديمي', 'رقم الجوال', 'عدد الساعات', 'اجر الساعه', 'المبلغ', 'رقم الايبان', 'البنك'];

    if (!empty($students_2h)) {
        echo '<Row><Cell ss:MergeAcross="8" ss:StyleID="sectionTitle"><Data ss:Type="String">طلاب الساعتين (2 ساعات يومياً)</Data></Cell></Row>';
        echo '<Row>'; foreach ($headers as $h) { echo '<Cell ss:StyleID="tableHeader"><Data ss:Type="String">' . $h . '</Data></Cell>'; } echo '</Row>';
        $c = 1; echo generateExcelRows($students_2h, $c);
        echo '<Row><Cell ss:MergeAcross="5" ss:StyleID="subtotalRow"><Data ss:Type="String">مجموع طلاب الساعتين</Data></Cell><Cell ss:StyleID="subtotalRow"><Data ss:Type="Number">' . $total_2h . '</Data></Cell><Cell ss:MergeAcross="1" ss:StyleID="subtotalRow"><Data ss:Type="String">' . numberToArabicWords($total_2h) . '</Data></Cell></Row>';
        echo '<Row/>';
    }
    if (!empty($students_3h)) {
        echo '<Row><Cell ss:MergeAcross="8" ss:StyleID="sectionTitle"><Data ss:Type="String">طلاب ثلاث ساعات (3 ساعات يومياً)</Data></Cell></Row>';
        echo '<Row>'; foreach ($headers as $h) { echo '<Cell ss:StyleID="tableHeader"><Data ss:Type="String">' . $h . '</Data></Cell>'; } echo '</Row>';
        $c = 1; echo generateExcelRows($students_3h, $c);
        echo '<Row><Cell ss:MergeAcross="5" ss:StyleID="subtotalRow"><Data ss:Type="String">مجموع طلاب ثلاث ساعات</Data></Cell><Cell ss:StyleID="subtotalRow"><Data ss:Type="Number">' . $total_3h . '</Data></Cell><Cell ss:MergeAcross="1" ss:StyleID="subtotalRow"><Data ss:Type="String">' . numberToArabicWords($total_3h) . '</Data></Cell></Row>';
        echo '<Row/>';
    }

    echo '<Row><Cell ss:MergeAcross="5" ss:StyleID="totalRow"><Data ss:Type="String">الإجمالي الكلي</Data></Cell><Cell ss:StyleID="totalRow"><Data ss:Type="Number">' . $total_amount . '</Data></Cell><Cell ss:MergeAcross="1" ss:StyleID="totalRow"><Data ss:Type="String">' . numberToArabicWords($total_amount) . '</Data></Cell></Row>';
    echo '<Row/><Row/>';
    echo '<Row><Cell/><Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">مكان التشغيل</Data></Cell><Cell ss:MergeAcross="5"><Data ss:Type="String">' . htmlspecialchars($sheet['work_place']) . '</Data></Cell></Row>';
    echo '<Row><Cell/><Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">المشرف المباشر</Data></Cell><Cell ss:MergeAcross="2"><Data ss:Type="String">' . htmlspecialchars($sheet['supervisor_name']) . '</Data></Cell><Cell ss:MergeAcross="2"><Data ss:Type="String">' . htmlspecialchars($sheet['signature_title']) . '</Data></Cell></Row>';
    echo '<Row><Cell/><Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">جوال المشرف المباشر</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($sheet['supervisor_phone']) . '</Data></Cell></Row>';
    echo '<Row><Cell/><Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">التوقيع</Data></Cell></Row>';

    echo '</Table></Worksheet></Workbook>';
    exit;
}

// ===== PRINT / VIEW MODE =====
$is_print = ($action == 'print');

if ($is_print) {
    // Standalone print page
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف حساب التشغيل — <?= $period_from_formatted ?></title>
    <style>
        body { font-family: 'Tahoma', 'Arial', sans-serif; direction: rtl; text-align: right; margin: 20px; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { border: 1px solid #000; padding: 8px; text-align: center; }
        th { background-color: #CCCCCC; font-weight: bold; }
        .total { background-color: #FFFF00; font-weight: bold; }
        .subtotal { background-color: #E8F5E9; font-weight: bold; }
        .section-title { background-color: #1b2d6b; color: white; font-weight: bold; font-size: 14px; padding: 10px; text-align: center; }
        .header { text-align: center; margin-bottom: 20px; }
        .footer-table { width: 100%; margin-top: 30px; }
        .footer-table td { border: none; padding: 8px; text-align: right; }
        .footer-label { font-weight: bold; width: 200px; }
        .no-print { }
        @media print { .no-print { display: none !important; } @page { size: A4 landscape; margin: 15mm; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; cursor: pointer; background: #2ebd70; color: white; border: none; border-radius: 6px; font-weight: bold;">🖨️ طباعة</button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 16px; cursor: pointer; background: #aaa; color: white; border: none; border-radius: 6px; font-weight: bold; margin-right: 10px;">✖ إغلاق</button>
    </div>
<?php } else {
    // View inside app layout
    include_once 'auth.php';
    requireAuth();
    include 'header.php';
?>
<div class="content">
    <h2 class="page-title" dir="rtl">📋 عرض كشف محفوظ</h2>

    <div style="text-align: center; margin-bottom: 20px;">
        <a href="view_salary.php?id=<?= $sheet_id ?>&action=print" target="_blank" class="btn btn-success" style="padding: 10px 25px;">🖨️ طباعة</a>
        <a href="view_salary.php?id=<?= $sheet_id ?>&action=excel" class="btn btn-success" style="padding: 10px 25px; background: #217346;">📊 تصدير Excel</a>
        <a href="edit_salary.php?id=<?= $sheet_id ?>" class="btn btn-warning" style="padding: 10px 25px;">✏️ تعديل</a>
        <a href="saved_sheets.php" class="btn btn-secondary" style="padding: 10px 25px;">⬅ العودة للكشوف</a>
    </div>

    <div style="background: #f4f6fb; padding: 20px; border-radius: 10px; margin-bottom: 20px;" dir="rtl">
        <table style="width: 100%; border: none;">
            <tr><td style="border: none; padding: 8px;"><strong>الفترة:</strong> <?= $period_from_formatted ?> — <?= $period_to_formatted ?></td></tr>
            <tr><td style="border: none; padding: 8px;"><strong>مكان التشغيل:</strong> <?= htmlspecialchars($sheet['work_place']) ?></td></tr>
            <tr><td style="border: none; padding: 8px;"><strong>المشرف:</strong> <?= htmlspecialchars($sheet['supervisor_name']) ?> | <?= htmlspecialchars($sheet['supervisor_phone']) ?></td></tr>
            <tr><td style="border: none; padding: 8px;"><strong>الإجمالي:</strong> <span style="color: #1b2d6b; font-size: 18px; font-weight: bold;"><?= number_format($total_amount, 2) ?> ريال</span></td></tr>
        </table>
    </div>
<?php } ?>

    <div class="header" style="text-align: center; margin-bottom: 20px;">
        <h2>قائم تشغيل نادي</h2>
        <p>خلال الفترة من <?= $period_from_formatted ?> إلى <?= $period_to_formatted ?> م</p>
    </div>

    <?php if (!empty($students_2h)): ?>
    <table>
        <thead>
            <tr><td colspan="9" class="section-title" style="background-color: #1b2d6b; color: white; font-weight: bold; padding: 10px; text-align: center;">طلاب الساعتين (2 ساعات يومياً)</td></tr>
            <tr>
                <th>م</th><th>الاسم</th><th>الرقم الاكاديمي</th><th>رقم الجوال</th>
                <th>عدد الساعات</th><th>اجر الساعه</th><th>المبلغ</th><th>رقم الايبان</th><th>البنك</th>
            </tr>
        </thead>
        <tbody>
            <?php $c = 1; foreach ($students_2h as $s): ?>
            <tr>
                <td><?= $c++ ?></td>
                <td><?= htmlspecialchars($s['student_name']) ?></td>
                <td><?= htmlspecialchars($s['academic_id']) ?></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><?= $s['hours'] ?></td>
                <td><?= $s['hourly_rate'] ?></td>
                <td><?= number_format($s['amount'], 2) ?></td>
                <td><?= htmlspecialchars($s['iban']) ?></td>
                <td><?= htmlspecialchars($s['bank_name']) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="subtotal" style="background-color: #E8F5E9; font-weight: bold;">
                <td colspan="6">مجموع طلاب الساعتين</td>
                <td><?= number_format($total_2h, 2) ?></td>
                <td colspan="2"><?= numberToArabicWords($total_2h) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <?php if (!empty($students_3h)): ?>
    <table>
        <thead>
            <tr><td colspan="9" class="section-title" style="background-color: #1b2d6b; color: white; font-weight: bold; padding: 10px; text-align: center;">طلاب ثلاث ساعات (3 ساعات يومياً)</td></tr>
            <tr>
                <th>م</th><th>الاسم</th><th>الرقم الاكاديمي</th><th>رقم الجوال</th>
                <th>عدد الساعات</th><th>اجر الساعه</th><th>المبلغ</th><th>رقم الايبان</th><th>البنك</th>
            </tr>
        </thead>
        <tbody>
            <?php $c = 1; foreach ($students_3h as $s): ?>
            <tr>
                <td><?= $c++ ?></td>
                <td><?= htmlspecialchars($s['student_name']) ?></td>
                <td><?= htmlspecialchars($s['academic_id']) ?></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><?= $s['hours'] ?></td>
                <td><?= $s['hourly_rate'] ?></td>
                <td><?= number_format($s['amount'], 2) ?></td>
                <td><?= htmlspecialchars($s['iban']) ?></td>
                <td><?= htmlspecialchars($s['bank_name']) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="subtotal" style="background-color: #E8F5E9; font-weight: bold;">
                <td colspan="6">مجموع طلاب ثلاث ساعات</td>
                <td><?= number_format($total_3h, 2) ?></td>
                <td colspan="2"><?= numberToArabicWords($total_3h) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endif; ?>

    <table>
        <tr class="total" style="background-color: #FFFF00; font-weight: bold;">
            <td colspan="6">الإجمالي الكلي</td>
            <td><?= number_format($total_amount, 2) ?></td>
            <td colspan="2"><?= numberToArabicWords($total_amount) ?></td>
        </tr>
    </table>

    <table class="footer-table" style="margin-top: 30px;">
        <tr><td class="footer-label" style="border: none; font-weight: bold; padding: 8px;">مكان التشغيل:</td><td colspan="2" style="border: none; padding: 8px;"><?= htmlspecialchars($sheet['work_place']) ?></td></tr>
        <tr><td class="footer-label" style="border: none; font-weight: bold; padding: 8px;">المشرف المباشر:</td><td style="border: none; padding: 8px;"><?= htmlspecialchars($sheet['supervisor_name']) ?></td><td style="border: none; padding: 8px;"><?= htmlspecialchars($sheet['signature_title']) ?></td></tr>
        <tr><td class="footer-label" style="border: none; font-weight: bold; padding: 8px;">جوال المشرف المباشر:</td><td colspan="2" style="border: none; padding: 8px;"><?= htmlspecialchars($sheet['supervisor_phone']) ?></td></tr>
        <tr><td class="footer-label" style="border: none; font-weight: bold; padding: 8px;">التوقيع:</td><td colspan="2" style="border: none;"></td></tr>
        <tr><td style="border: none;"></td><td colspan="2" style="border: none; text-align: center; padding-top: 30px; font-size: 16px;"><?= htmlspecialchars($sheet['signature_name']) ?></td></tr>
    </table>

<?php if ($is_print): ?>
</body></html>
<?php } else { ?>
</div>
<?php include 'footer.php'; } ?>
