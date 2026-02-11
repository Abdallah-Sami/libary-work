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
    // Excel XML Format
    $filename = 'salary_sheet_' . date('Y-m-d') . '.xls';
    
    header('Content-Type: application/vnd.ms-excel; charset=UTF-8');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    echo '<?xml version="1.0" encoding="UTF-8"?>';
    echo '<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:html="http://www.w3.org/TR/REC-html40">';
    
    // Styles
    echo '<Styles>';
    echo '<Style ss:ID="header"><Font ss:Bold="1" ss:Size="14"/><Alignment ss:Horizontal="Center"/></Style>';
    echo '<Style ss:ID="subheader"><Alignment ss:Horizontal="Center"/></Style>';
    echo '<Style ss:ID="tableHeader"><Font ss:Bold="1"/><Alignment ss:Horizontal="Center"/><Interior ss:Color="#CCCCCC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
    echo '<Style ss:ID="tableCell"><Alignment ss:Horizontal="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
    echo '<Style ss:ID="totalRow"><Font ss:Bold="1"/><Interior ss:Color="#FFFF00" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>';
    echo '<Style ss:ID="footerLabel"><Font ss:Bold="1"/></Style>';
    echo '</Styles>';
    
    echo '<Worksheet ss:Name="كشف الحساب" ss:RightToLeft="1">';
    echo '<Table>';
    
    // Column widths
    echo '<Column ss:Width="40"/>';  // م
    echo '<Column ss:Width="150"/>'; // الاسم
    echo '<Column ss:Width="100"/>'; // الرقم الأكاديمي
    echo '<Column ss:Width="100"/>'; // رقم الجوال
    echo '<Column ss:Width="80"/>';  // عدد الساعات
    echo '<Column ss:Width="80"/>';  // أجر الساعة
    echo '<Column ss:Width="80"/>';  // المبلغ
    echo '<Column ss:Width="200"/>'; // الآيبان
    echo '<Column ss:Width="100"/>'; // البنك
    
    // Title row
    echo '<Row ss:Height="25">';
    echo '<Cell ss:MergeAcross="8" ss:StyleID="header"><Data ss:Type="String">قائم تشغيل نادي</Data></Cell>';
    echo '</Row>';
    
    // Period row
    echo '<Row>';
    echo '<Cell ss:MergeAcross="8" ss:StyleID="subheader"><Data ss:Type="String">خلال الفترة من ' . $period_from_formatted . ' إلى ' . $period_to_formatted . ' م</Data></Cell>';
    echo '</Row>';
    
    // Empty row
    echo '<Row/>';
    
    // Headers
    echo '<Row>';
    $headers = ['م', 'الاسم', 'الرقم الاكاديمي', 'رقم الجوال', 'عدد الساعات', 'اجر الساعه', 'المبلغ', 'رقم الايبان', 'البنك'];
    foreach ($headers as $header) {
        echo '<Cell ss:StyleID="tableHeader"><Data ss:Type="String">' . htmlspecialchars($header) . '</Data></Cell>';
    }
    echo '</Row>';
    
    // Data rows
    $counter = 1;
    foreach ($students_data as $student) {
        echo '<Row>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $counter++ . '</Data></Cell>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($student['name']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($student['academic_id']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($student['phone']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $student['hours'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $student['rate'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $student['amount'] . '</Data></Cell>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($student['iban']) . '</Data></Cell>';
        echo '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($student['bank']) . '</Data></Cell>';
        echo '</Row>';
    }
    
    // Total row
    echo '<Row>';
    echo '<Cell ss:MergeAcross="5" ss:StyleID="totalRow"><Data ss:Type="String">الإجمالي</Data></Cell>';
    echo '<Cell ss:StyleID="totalRow"><Data ss:Type="Number">' . $total_amount . '</Data></Cell>';
    echo '<Cell ss:MergeAcross="1" ss:StyleID="totalRow"><Data ss:Type="String">' . numberToArabicWords($total_amount) . '</Data></Cell>';
    echo '</Row>';
    
    // Empty rows
    echo '<Row/>';
    echo '<Row/>';
    
    // Footer
    echo '<Row>';
    echo '<Cell/>';
    echo '<Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">مكان التشغيل</Data></Cell>';
    echo '<Cell ss:MergeAcross="5"><Data ss:Type="String">' . htmlspecialchars($work_place) . '</Data></Cell>';
    echo '</Row>';
    
    echo '<Row>';
    echo '<Cell/>';
    echo '<Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">المشرف المباشر</Data></Cell>';
    echo '<Cell ss:MergeAcross="2"><Data ss:Type="String">' . htmlspecialchars($supervisor_name) . '</Data></Cell>';
    echo '<Cell ss:MergeAcross="2"><Data ss:Type="String">' . htmlspecialchars($signature_title) . '</Data></Cell>';
    echo '</Row>';
    
    echo '<Row>';
    echo '<Cell/>';
    echo '<Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">جوال المشرف المباشر</Data></Cell>';
    echo '<Cell><Data ss:Type="String">' . htmlspecialchars($supervisor_phone) . '</Data></Cell>';
    echo '</Row>';
    
    echo '<Row>';
    echo '<Cell/>';
    echo '<Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">التوقيع</Data></Cell>';
    echo '</Row>';
    
    echo '<Row/>';
    
    echo '<Row>';
    echo '<Cell/><Cell/><Cell/><Cell/><Cell/>';
    echo '<Cell ss:MergeAcross="1"><Data ss:Type="String">' . htmlspecialchars($signature_name) . '</Data></Cell>';
    echo '</Row>';
    
    echo '</Table>';
    echo '</Worksheet>';
    echo '</Workbook>';
    
    exit;
    
} else {
    // PDF Export (same as before)
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
            .footer-table {
                width: 100%;
                margin-top: 30px;
            }
            .footer-table td {
                border: none;
                padding: 8px;
                text-align: right;
            }
            .footer-label {
                font-weight: bold;
                width: 200px;
            }
            @media print {
                button { display: none; }
                @page {
                    size: A4 landscape;
                    margin: 15mm;
                }
            }
        </style>
    </head>
    <body>
        <button onclick="window.print()" style="padding: 10px 20px; font-size: 16px; margin-bottom: 20px; cursor: pointer;">🖨️ طباعة PDF</button>
        
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
                    <td><?= number_format($total_amount, 2) ?></td>
                    <td colspan="2"><?= numberToArabicWords($total_amount) ?></td>
                </tr>
            </tbody>
        </table>
        
        <table class="footer-table">
            <tr>
                <td class="footer-label">مكان التشغيل:</td>
                <td colspan="2"><?= htmlspecialchars($work_place) ?></td>
            </tr>
            <tr>
                <td class="footer-label">المشرف المباشر:</td>
                <td><?= htmlspecialchars($supervisor_name) ?></td>
                <td><?= htmlspecialchars($signature_title) ?></td>
            </tr>
            <tr>
                <td class="footer-label">جوال المشرف المباشر:</td>
                <td colspan="2"><?= htmlspecialchars($supervisor_phone) ?></td>
            </tr>
            <tr>
                <td class="footer-label">التوقيع:</td>
                <td colspan="2"></td>
            </tr>
            <tr>
                <td></td>
                <td colspan="2" style="text-align: center; padding-top: 30px; font-size: 16px;">
                    <?= htmlspecialchars($signature_name) ?>
                </td>
            </tr>
        </table>
    </body>
    </html>
    <?php
    exit;
}
?>
