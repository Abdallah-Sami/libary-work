<?php
require_once 'db_connect.php';
require_once 'helpers.php';

$period_from = $_POST['period_from'] ?? '';
$period_to = $_POST['period_to'] ?? '';
$work_place = $_POST['work_place'] ?? '';
$supervisor_name = $_POST['supervisor_name'] ?? '';
$supervisor_phone = $_POST['supervisor_phone'] ?? '';
$signature_name = $_POST['signature_name'] ?? '';
$signature_title = $_POST['signature_title'] ?? '';
$export_type = $_POST['export_type'] ?? 'excel';
$selected_students = $_POST['students'] ?? [];

if (empty($selected_students)) {
    die("<script>alert('الرجاء اختيار طالب واحد على الأقل!'); window.history.back();</script>");
}

$data = collectSalaryStudents($conn, $selected_students);
extract($data);
$total_amount = $total_2h + $total_3h;

if (empty($students_2h) && empty($students_3h)) {
    die("<script>alert('الرجاء إدخال عدد الساعات للطلاب المختارين!'); window.history.back();</script>");
}

$period_from_formatted = date('d/m/Y', strtotime($period_from));
$period_to_formatted = date('d/m/Y', strtotime($period_to));

if ($export_type == 'excel') {
    $filename = 'salary_sheet_' . date('Y-m-d') . '.xls';
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');

    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
    echo excelXmlStyles();
    echo '<Worksheet ss:Name="كشف الحساب" ss:RightToLeft="1"><Table>';
    echo excelXmlColumns();
    echo excelXmlHeader($period_from_formatted, $period_to_formatted);

    if (!empty($students_2h)) echo excelXmlSection('طلاب الساعتين (2 ساعات يومياً)', $students_2h, $total_2h);
    if (!empty($students_3h)) echo excelXmlSection('طلاب ثلاث ساعات (3 ساعات يومياً)', $students_3h, $total_3h);

    echo excelXmlGrandTotal($total_amount);
    echo excelXmlFooter($work_place, $supervisor_name, $supervisor_phone, $signature_title, $signature_name);
    echo '</Table></Worksheet></Workbook>';
    exit;
}

// PDF Export (HTML print)
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>كشف حساب التشغيل</title>
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
        @media print { button { display: none; } @page { size: A4 landscape; margin: 15mm; } }
    </style>
</head>
<body>
    <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; margin-bottom: 20px; cursor: pointer;">🖨️ طباعة PDF</button>

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
            <tr><td colspan="9" class="section-title"><?= $sec['title'] ?></td></tr>
            <tr><th>م</th><th>الاسم</th><th>الرقم الاكاديمي</th><th>رقم الجوال</th><th>عدد الساعات</th><th>اجر الساعه</th><th>المبلغ</th><th>رقم الايبان</th><th>البنك</th></tr>
        </thead>
        <tbody>
            <?php $c = 1; foreach ($sec['students'] as $s): ?>
            <tr>
                <td><?= $c++ ?></td>
                <td><?= htmlspecialchars($s['name']) ?></td>
                <td><?= htmlspecialchars($s['academic_id']) ?></td>
                <td><?= htmlspecialchars($s['phone']) ?></td>
                <td><?= $s['hours'] ?></td>
                <td><?= $s['rate'] ?></td>
                <td><?= $s['amount'] ?></td>
                <td><?= htmlspecialchars($s['iban']) ?></td>
                <td><?= htmlspecialchars($s['bank']) ?></td>
            </tr>
            <?php endforeach; ?>
            <tr class="subtotal">
                <td colspan="6">مجموع <?= $sec['title'] ?></td>
                <td><?= number_format($sec['total'], 2) ?></td>
                <td colspan="2"><?= numberToArabicWords($sec['total']) ?></td>
            </tr>
        </tbody>
    </table>
    <?php endforeach; ?>

    <table>
        <tr class="total">
            <td colspan="6">الإجمالي الكلي</td>
            <td><?= number_format($total_amount, 2) ?></td>
            <td colspan="2"><?= numberToArabicWords($total_amount) ?></td>
        </tr>
    </table>

    <table class="footer-table">
        <tr><td class="footer-label">مكان التشغيل:</td><td colspan="2"><?= htmlspecialchars($work_place) ?></td></tr>
        <tr><td class="footer-label">المشرف المباشر:</td><td><?= htmlspecialchars($supervisor_name) ?></td><td><?= htmlspecialchars($signature_title) ?></td></tr>
        <tr><td class="footer-label">جوال المشرف المباشر:</td><td colspan="2"><?= htmlspecialchars($supervisor_phone) ?></td></tr>
        <tr><td class="footer-label">التوقيع:</td><td colspan="2"></td></tr>
        <tr><td></td><td colspan="2" style="text-align: center; padding-top: 30px; font-size: 16px;"><?= htmlspecialchars($signature_name) ?></td></tr>
    </table>
</body>
</html>
<?php exit; ?>
