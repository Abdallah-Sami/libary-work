<?php
require_once 'db_connect.php';

// Get form data
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

// Get student data
$students_data = [];
$total_amount = 0;

foreach ($selected_students as $student_id) {
    $student_id = intval($student_id);
    $hours = floatval($_POST['hours_' . $student_id] ?? 0);
    
    if ($hours > 0) {
        $sql = "SELECT * FROM student_workers WHERE id = $student_id";
        $result = $conn->query($sql);
        
        if ($result && $result->num_rows > 0) {
            $student = $result->fetch_assoc();
            $amount = $hours * $student['hourly_rate'];
            $total_amount += $amount;
            
            $students_data[] = [
                'name' => $student['full_name'],
                'academic_id' => $student['academic_id'],
                'phone' => $student['phone'],
                'hours' => $hours,
                'rate' => $student['hourly_rate'],
                'amount' => $amount,
                'iban' => $student['iban'] ?? '',
                'bank' => $student['bank_name'] ?? ''
            ];
        }
    }
}

if (empty($students_data)) {
    die("<script>alert('الرجاء إدخال عدد الساعات للطلاب المختارين!'); window.history.back();</script>");
}

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
    if ($thou > 0) {
        $result .= $thousands[$thou] . ' ';
        $number = $number % 1000;
    }
    
    $hund = floor($number / 100);
    if ($hund > 0) {
        $result .= $hundreds[$hund] . ' و';
        $number = $number % 100;
    }
    
    $ten = floor($number / 10);
    if ($ten > 1) {
        $result .= $tens[$ten] . ' و';
        $number = $number % 10;
    }
    
    if ($number > 0) {
        $result .= $ones[$number];
    }
    
    return trim($result, ' و') . ' ريال سعودي';
}

// Format dates
$period_from_formatted = date('d/m/Y', strtotime($period_from));
$period_to_formatted = date('d/m/Y', strtotime($period_to));

if ($export_type == 'excel') {
    // Simple CSV Export (opens in Excel)
    $filename = 'salary_sheet_' . date('Y-m-d') . '.csv';
    
    header('Content-Type: text/csv; charset=UTF-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    // Add BOM for UTF-8
    echo "\xEF\xBB\xBF";
    
    $output = fopen('php://output', 'w');
    
    // Title
    fputcsv($output, ['قائم تشغيل نادي']);
    fputcsv($output, ['خلال الفترة من ' . $period_from_formatted . ' إلى ' . $period_to_formatted . ' م']);
    fputcsv($output, []); // Empty row
    
    // Headers
    fputcsv($output, ['م', 'الاسم', 'الرقم الاكاديمي', 'رقم الجوال', 'عدد الساعات', 'اجر الساعه', 'المبلغ', 'رقم الايبان', 'البنك']);
    
    // Data
    $counter = 1;
    foreach ($students_data as $student) {
        fputcsv($output, [
            $counter++,
            $student['name'],
            $student['academic_id'],
            $student['phone'],
            $student['hours'],
            $student['rate'],
            $student['amount'],
            $student['iban'],
            $student['bank']
        ]);
    }
    
    // Total
    fputcsv($output, ['الإجمالي', '', '', '', '', '', $total_amount, numberToArabicWords($total_amount), '']);
    
    fputcsv($output, []); // Empty row
    
    // Footer
    fputcsv($output, ['مكان التشغيل', $work_place]);
    fputcsv($output, ['المشرف المباشر', $supervisor_name, '', '', '', $signature_title]);
    fputcsv($output, ['جوال المشرف المباشر', $supervisor_phone]);
    fputcsv($output, ['التوقيع']);
    fputcsv($output, ['', '', '', '', '', $signature_name]);
    
    fclose($output);
    exit;
    
} else {
    // PDF Export (HTML to PDF)
    ?>
    <!DOCTYPE html>
    <html lang="ar" dir="rtl">
    <head>
        <meta charset="UTF-8">
        <title>كشف حساب التشغيل</title>
        <style>
            body {
                font-family: 'Tahoma', 'Arial', sans-serif;
                direction: rtl;
                text-align: right;
                margin: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
                margin: 20px 0;
            }
            th, td {
                border: 1px solid #000;
                padding: 8px;
                text-align: center;
            }
            th {
                background-color: #CCCCCC;
                font-weight: bold;
            }
            .total {
                background-color: #FFFF00;
                font-weight: bold;
            }
            .header {
                text-align: center;
                margin-bottom: 20px;
            }
            @media print {
                button { display: none; }
            }
        </style>
    </head>
    <body>
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; margin-bottom: 20px;">🖨️ طباعة PDF</button>
        
        <div class="header">
            <h2>قائم تشغيل نادي</h2>
            <p>خلال الفترة من <?= $period_from_formatted ?> إلى <?= $period_to_formatted ?> م</p>
        </div>
        
        <table>
            <thead>
                <tr>
                    <th>م</th>
                    <th>الاسم</th>
                    <th>الرقم الاكاديمي</th>
                    <th>رقم الجوال</th>
                    <th>عدد الساعات</th>
                    <th>اجر الساعه</th>
                    <th>المبلغ</th>
                    <th>رقم الايبان</th>
                    <th>البنك</th>
                </tr>
            </thead>
            <tbody>
                <?php $counter = 1; ?>
                <?php foreach ($students_data as $student): ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td><?= htmlspecialchars($student['name']) ?></td>
                    <td><?= htmlspecialchars($student['academic_id']) ?></td>
                    <td><?= htmlspecialchars($student['phone']) ?></td>
                    <td><?= $student['hours'] ?></td>
                    <td><?= $student['rate'] ?></td>
                    <td><?= $student['amount'] ?></td>
                    <td><?= htmlspecialchars($student['iban']) ?></td>
                    <td><?= htmlspecialchars($student['bank']) ?></td>
                </tr>
                <?php endforeach; ?>
                <tr class="total">
                    <td colspan="6">الإجمالي</td>
                    <td><?= $total_amount ?></td>
                    <td colspan="2"><?= numberToArabicWords($total_amount) ?></td>
                </tr>
            </tbody>
        </table>
        
        <div style="margin-top: 30px;">
            <table style="border: none;">
                <tr>
                    <td style="border: none; width: 200px;"><strong>مكان التشغيل:</strong></td>
                    <td style="border: none;"><?= htmlspecialchars($work_place) ?></td>
                </tr>
                <tr>
                    <td style="border: none;"><strong>المشرف المباشر:</strong></td>
                    <td style="border: none;"><?= htmlspecialchars($supervisor_name) ?></td>
                    <td style="border: none;"><?= htmlspecialchars($signature_title) ?></td>
                </tr>
                <tr>
                    <td style="border: none;"><strong>جوال المشرف:</strong></td>
                    <td style="border: none;"><?= htmlspecialchars($supervisor_phone) ?></td>
                </tr>
            </table>
        </div>
        
        <div style="margin-top: 40px;">
            <p><strong>التوقيع:</strong></p>
            <p style="margin-top: 50px; text-align: center; font-size: 18px;">
                <?= htmlspecialchars($signature_name) ?>
            </p>
        </div>
        
        <script>
            // Auto print when page loads
            window.onload = function() {
                setTimeout(function() {
                    // Uncomment to auto-print
                    // window.print();
                }, 500);
            }
        </script>
    </body>
    </html>
    <?php
    exit;
}
?>
