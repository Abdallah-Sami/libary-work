<?php
require_once 'db_connect.php';

// Auto-create tables
$conn->query("CREATE TABLE IF NOT EXISTS salary_sheets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    period_from DATE NOT NULL,
    period_to DATE NOT NULL,
    work_place VARCHAR(255),
    supervisor_name VARCHAR(255),
    supervisor_phone VARCHAR(50),
    signature_name VARCHAR(255),
    signature_title VARCHAR(255),
    total_amount DECIMAL(10,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

$conn->query("CREATE TABLE IF NOT EXISTS salary_sheet_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sheet_id INT NOT NULL,
    student_id INT NOT NULL,
    student_name VARCHAR(255),
    academic_id VARCHAR(100),
    phone VARCHAR(50),
    hours DECIMAL(10,2),
    hourly_rate DECIMAL(10,2),
    amount DECIMAL(10,2),
    iban VARCHAR(100),
    bank_name VARCHAR(100),
    daily_hours INT DEFAULT 2,
    FOREIGN KEY (sheet_id) REFERENCES salary_sheets(id) ON DELETE CASCADE
)");

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

// Collect student data and calculate total
$items = [];
$total_amount = 0;

foreach ($selected_students as $student_id) {
    $student_id = intval($student_id);
    $hours = floatval($_POST['hours_' . $student_id] ?? 0);
    $daily_hours = intval($_POST['daily_hours_' . $student_id] ?? 2);

    if ($hours > 0) {
        $result = $conn->query("SELECT * FROM student_workers WHERE id = $student_id");
        if ($result && $result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $amount = $hours * $student['hourly_rate'];
            $total_amount += $amount;
            $items[] = [
                'student_id' => $student_id,
                'student_name' => $student['full_name'],
                'academic_id' => $student['academic_id'],
                'phone' => $student['phone'],
                'hours' => $hours,
                'hourly_rate' => $student['hourly_rate'],
                'amount' => $amount,
                'iban' => $student['iban'] ?? '',
                'bank_name' => $student['bank_name'] ?? '',
                'daily_hours' => $daily_hours
            ];
        }
    }
}

if (empty($items)) {
    die("<script>alert('الرجاء إدخال عدد الساعات للطلاب المختارين!'); window.history.back();</script>");
}

// Check if editing existing sheet
$edit_id = intval($_POST['edit_sheet_id'] ?? 0);

if ($edit_id > 0) {
    // Update existing sheet
    $conn->query("UPDATE salary_sheets SET
        period_from='$period_from',
        period_to='$period_to',
        work_place='$work_place',
        supervisor_name='$supervisor_name',
        supervisor_phone='$supervisor_phone',
        signature_name='$signature_name',
        signature_title='$signature_title',
        total_amount='$total_amount'
        WHERE id=$edit_id");
    $sheet_id = $edit_id;

    // Delete old items
    $conn->query("DELETE FROM salary_sheet_items WHERE sheet_id = $sheet_id");
} else {
    // Insert new sheet
    $conn->query("INSERT INTO salary_sheets (period_from, period_to, work_place, supervisor_name, supervisor_phone, signature_name, signature_title, total_amount)
        VALUES ('$period_from', '$period_to', '$work_place', '$supervisor_name', '$supervisor_phone', '$signature_name', '$signature_title', '$total_amount')");
    $sheet_id = $conn->insert_id;
}

// Insert items
foreach ($items as $item) {
    $sn = $conn->real_escape_string($item['student_name']);
    $aid = $conn->real_escape_string($item['academic_id']);
    $ph = $conn->real_escape_string($item['phone']);
    $ib = $conn->real_escape_string($item['iban']);
    $bn = $conn->real_escape_string($item['bank_name']);

    $conn->query("INSERT INTO salary_sheet_items (sheet_id, student_id, student_name, academic_id, phone, hours, hourly_rate, amount, iban, bank_name, daily_hours)
        VALUES ($sheet_id, {$item['student_id']}, '$sn', '$aid', '$ph', {$item['hours']}, {$item['hourly_rate']}, {$item['amount']}, '$ib', '$bn', {$item['daily_hours']})");
}

$action = $edit_id > 0 ? 'تم تحديث' : 'تم حفظ';
echo "<script>alert('✅ $action الكشف بنجاح!'); window.location='saved_sheets.php';</script>";
?>
