<?php
require_once 'db_connect.php';
require_once 'helpers.php';

ensureSalaryTables($conn);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: salary_sheet.php');
    exit;
}

$period_from = $conn->real_escape_string($_POST['period_from'] ?? '');
$period_to = $conn->real_escape_string($_POST['period_to'] ?? '');
$work_place = $conn->real_escape_string($_POST['work_place'] ?? '');
$supervisor_name = $conn->real_escape_string($_POST['supervisor_name'] ?? '');
$supervisor_phone = $conn->real_escape_string($_POST['supervisor_phone'] ?? '');
$signature_name = $conn->real_escape_string($_POST['signature_name'] ?? '');
$signature_title = $conn->real_escape_string($_POST['signature_title'] ?? '');
$selected_students = $_POST['students'] ?? [];

if (empty($selected_students)) {
    die("<script>alert('الرجاء اختيار طالب واحد على الأقل!'); window.history.back();</script>");
}

$data = collectSalaryStudents($conn, $selected_students);
$items = array_merge($data['students_2h'], $data['students_3h']);
$total_amount = $data['total_2h'] + $data['total_3h'];

if (empty($items)) {
    die("<script>alert('الرجاء إدخال عدد الساعات للطلاب المختارين!'); window.history.back();</script>");
}

$edit_id = intval($_POST['edit_sheet_id'] ?? 0);

if ($edit_id > 0) {
    $conn->query("UPDATE salary_sheets SET
        period_from='$period_from', period_to='$period_to', work_place='$work_place',
        supervisor_name='$supervisor_name', supervisor_phone='$supervisor_phone',
        signature_name='$signature_name', signature_title='$signature_title',
        total_amount='$total_amount' WHERE id=$edit_id");
    $sheet_id = $edit_id;
    $conn->query("DELETE FROM salary_sheet_items WHERE sheet_id = $sheet_id");
} else {
    $conn->query("INSERT INTO salary_sheets (period_from, period_to, work_place, supervisor_name, supervisor_phone, signature_name, signature_title, total_amount)
        VALUES ('$period_from', '$period_to', '$work_place', '$supervisor_name', '$supervisor_phone', '$signature_name', '$signature_title', '$total_amount')");
    $sheet_id = $conn->insert_id;
}

foreach ($items as $item) {
    $sn = $conn->real_escape_string($item['name']);
    $aid = $conn->real_escape_string($item['academic_id']);
    $ph = $conn->real_escape_string($item['phone']);
    $ib = $conn->real_escape_string($item['iban']);
    $bn = $conn->real_escape_string($item['bank_name']);
    $dh = intval($item['daily_hours']);
    $conn->query("INSERT INTO salary_sheet_items (sheet_id, student_id, student_name, academic_id, phone, hours, hourly_rate, amount, iban, bank_name, daily_hours)
        VALUES ($sheet_id, {$item['student_id']}, '$sn', '$aid', '$ph', {$item['hours']}, {$item['hourly_rate']}, {$item['amount']}, '$ib', '$bn', $dh)");
}

$action = $edit_id > 0 ? 'تم تحديث' : 'تم حفظ';
echo "<script>alert('✅ $action الكشف بنجاح!'); window.location='saved_sheets.php';</script>";
?>
