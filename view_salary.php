<?php
require_once 'db_connect.php';
require_once 'helpers.php';

$sheet_id = intval($_GET['id'] ?? 0);
$action = $_GET['action'] ?? 'view';

if ($sheet_id <= 0) {
    die("<script>alert('كشف غير صالح!'); window.location='saved_sheets.php';</script>");
}

$sheet_result = $conn->query("SELECT * FROM salary_sheets WHERE id = $sheet_id");
if (!$sheet_result || $sheet_result->num_rows == 0) {
    die("<script>alert('الكشف غير موجود!'); window.location='saved_sheets.php';</script>");
}
$sheet = $sheet_result->fetch_assoc();

// Get items separated by daily hours
$items_2h = $conn->query("SELECT * FROM salary_sheet_items WHERE sheet_id = $sheet_id AND (daily_hours = 2 OR daily_hours IS NULL) ORDER BY id ASC");
$items_3h = $conn->query("SELECT * FROM salary_sheet_items WHERE sheet_id = $sheet_id AND daily_hours = 3 ORDER BY id ASC");

$students_2h = []; $students_3h = []; $total_2h = 0; $total_3h = 0;
if ($items_2h) { while ($row = $items_2h->fetch_assoc()) { $students_2h[] = $row; $total_2h += $row['amount']; } }
if ($items_3h) { while ($row = $items_3h->fetch_assoc()) { $students_3h[] = $row; $total_3h += $row['amount']; } }

$total_amount = $total_2h + $total_3h;
$period_from_formatted = date('d/m/Y', strtotime($sheet['period_from']));
$period_to_formatted = date('d/m/Y', strtotime($sheet['period_to']));

// ===== EXCEL EXPORT =====
if ($action == 'excel') {
    $filename = 'salary_sheet_' . $sheet['period_from'] . '_' . $sheet['period_to'] . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
    echo excelXmlStyles();
    echo '<Worksheet ss:Name="كشف الحساب" ss:RightToLeft="1"><Table>';
    echo excelXmlColumns();
    echo excelXmlHeader($period_from_formatted, $period_to_formatted);

    if (!empty($students_2h)) echo excelXmlSection('طلاب الساعتين (2 ساعات يومياً)', $students_2h, $total_2h, 'student_name');
    if (!empty($students_3h)) echo excelXmlSection('طلاب ثلاث ساعات (3 ساعات يومياً)', $students_3h, $total_3h, 'student_name');

    echo excelXmlGrandTotal($total_amount);
    echo excelXmlFooter($sheet['work_place'], $sheet['supervisor_name'], $sheet['supervisor_phone'], $sheet['signature_title'], $sheet['signature_name']);
    echo '</Table></Worksheet></Workbook>';
    exit;
}

// ===== PRINT / VIEW MODE =====
$is_print = ($action == 'print');

if ($is_print) {
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
        .footer-table { width: 100%; margin-top: 30px; }
        .footer-table td { border: none; padding: 8px; text-align: right; }
        .footer-label { font-weight: bold; width: 200px; }
        @media print { .no-print { display: none !important; } @page { size: A4 landscape; margin: 15mm; } }
    </style>
</head>
<body>
    <div class="no-print" style="text-align: center; margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 10px 30px; font-size: 16px; cursor: pointer; background: #2ebd70; color: white; border: none; border-radius: 6px; font-weight: bold;">🖨️ طباعة</button>
        <button onclick="window.close()" style="padding: 10px 30px; font-size: 16px; cursor: pointer; background: #aaa; color: white; border: none; border-radius: 6px; font-weight: bold; margin-right: 10px;">✖ إغلاق</button>
    </div>
<?php } else {
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

    <div style="text-align: center; margin-bottom: 20px;">
        <h2>قائم تشغيل نادي</h2>
        <p>خلال الفترة من <?= $period_from_formatted ?> إلى <?= $period_to_formatted ?> م</p>
    </div>

    <?php
    $sections = [];
    if (!empty($students_2h)) $sections[] = ['title' => 'طلاب الساعتين (2 ساعات يومياً)', 'students' => $students_2h, 'total' => $total_2h];
    if (!empty($students_3h)) $sections[] = ['title' => 'طلاب ثلاث ساعات (3 ساعات يومياً)', 'students' => $students_3h, 'total' => $total_3h];

    foreach ($sections as $sec): ?>
    <table>
        <thead>
            <tr><td colspan="9" class="section-title" style="background-color: #1b2d6b; color: white; font-weight: bold; padding: 10px; text-align: center;"><?= $sec['title'] ?></td></tr>
            <tr><th>م</th><th>الاسم</th><th>الرقم الاكاديمي</th><th>رقم الجوال</th><th>عدد الساعات</th><th>اجر الساعه</th><th>المبلغ</th><th>رقم الايبان</th><th>البنك</th></tr>
        </thead>
        <tbody>
            <?php $c = 1; foreach ($sec['students'] as $s): ?>
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
                <td colspan="6">مجموع <?= $sec['title'] ?></td>
                <td><?= number_format($sec['total'], 2) ?></td>
                <td colspan="2"><?= numberToArabicWords($sec['total']) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endforeach; ?>

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
<?php else: ?>
</div>
<?php include 'footer.php'; endif; ?>
