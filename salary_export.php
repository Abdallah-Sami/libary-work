<?php
require_once 'db_connect.php';
require_once 'autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

// Get form data
$period_from = $_POST['period_from'];
$period_to = $_POST['period_to'];
$work_place = $_POST['work_place'];
$supervisor_name = $_POST['supervisor_name'];
$supervisor_phone = $_POST['supervisor_phone'];
$signature_name = $_POST['signature_name'] ?? '';
$signature_title = $_POST['signature_title'] ?? '';
$export_type = $_POST['export_type'];
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

// Convert number to Arabic words (simplified)
function numberToArabicWords($number) {
    $number = intval($number);
    
    $ones = ['', 'واحد', 'اثنان', 'ثلاثة', 'أربعة', 'خمسة', 'ستة', 'سبعة', 'ثمانية', 'تسعة'];
    $tens = ['', 'عشرة', 'عشرون', 'ثلاثون', 'أربعون', 'خمسون', 'ستون', 'سبعون', 'ثمانون', 'تسعون'];
    $hundreds = ['', 'مئة', 'مئتان', 'ثلاثمئة', 'أربعمئة', 'خمسمئة', 'ستمئة', 'سبعمئة', 'ثمانمئة', 'تسعمئة'];
    $thousands = ['', 'ألف', 'ألفان', 'ثلاثة آلاف', 'أربعة آلاف', 'خمسة آلاف', 'ستة آلاف', 'سبعة آلاف', 'ثمانية آلاف', 'تسعة آلاف'];
    
    if ($number == 0) return 'صفر';
    
    $result = '';
    
    // Thousands
    $thou = floor($number / 1000);
    if ($thou > 0) {
        $result .= $thousands[$thou] . ' ';
        $number = $number % 1000;
    }
    
    // Hundreds
    $hund = floor($number / 100);
    if ($hund > 0) {
        $result .= $hundreds[$hund] . ' و';
        $number = $number % 100;
    }
    
    // Tens
    $ten = floor($number / 10);
    if ($ten > 1) {
        $result .= $tens[$ten] . ' و';
        $number = $number % 10;
    }
    
    // Ones
    if ($number > 0) {
        $result .= $ones[$number];
    }
    
    return trim($result, ' و') . ' ريال سعودي';
}

// Format date
$period_from_formatted = date('d/m/Y', strtotime($period_from));
$period_to_formatted = date('d/m/Y', strtotime($period_to));

if ($export_type == 'excel') {
    // Excel Export
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();
    $sheet->setRightToLeft(true);
    
    // Title
    $sheet->mergeCells('A3:I3');
    $sheet->setCellValue('A3', 'قائم تشغيل نادي');
    $sheet->getStyle('A3')->getFont()->setBold(true)->setSize(14);
    $sheet->getStyle('A3')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Period
    $sheet->mergeCells('A4:I4');
    $sheet->setCellValue('A4', 'خلال الفترة من ' . $period_from_formatted . ' إلى ' . $period_to_formatted . ' م');
    $sheet->getStyle('A4')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    
    // Headers
    $row = 5;
    $headers = ['م', 'الاسم', 'الرقم الاكاديمي', 'رقم الجوال', 'عدد الساعات', 'اجر الساعه', 'المبلغ', 'رقم الايبان', 'البنك'];
    $col = 'A';
    foreach ($headers as $header) {
        $sheet->setCellValue($col . $row, $header);
        $sheet->getStyle($col . $row)->getFont()->setBold(true);
        $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        $sheet->getStyle($col . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('CCCCCC');
        $col++;
    }
    
    // Data
    $row = 6;
    $counter = 1;
    foreach ($students_data as $student) {
        $sheet->setCellValue('A' . $row, $counter++);
        $sheet->setCellValue('B' . $row, $student['name']);
        $sheet->setCellValue('C' . $row, $student['academic_id']);
        $sheet->setCellValue('D' . $row, $student['phone']);
        $sheet->setCellValue('E' . $row, $student['hours']);
        $sheet->setCellValue('F' . $row, $student['rate']);
        $sheet->setCellValue('G' . $row, $student['amount']);
        $sheet->setCellValue('H' . $row, $student['iban']);
        $sheet->setCellValue('I' . $row, $student['bank']);
        
        // Center align
        foreach (range('A', 'I') as $col) {
            $sheet->getStyle($col . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }
        
        $row++;
    }
    
    // Total
    $sheet->setCellValue('A' . $row, 'الإجمالي');
    $sheet->mergeCells('A' . $row . ':F' . $row);
    $sheet->setCellValue('G' . $row, $total_amount);
    $sheet->setCellValue('H' . $row, numberToArabicWords($total_amount));
    $sheet->mergeCells('H' . $row . ':I' . $row);
    $sheet->getStyle('A' . $row . ':I' . $row)->getFont()->setBold(true);
    $sheet->getStyle('A' . $row . ':I' . $row)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FFFF00');
    
    $row += 2;
    
    // Footer info
    $sheet->mergeCells('B' . $row . ':C' . $row);
    $sheet->setCellValue('B' . $row, 'مكان التشغيل');
    $sheet->mergeCells('D' . $row . ':F' . $row);
    $sheet->setCellValue('D' . $row, $work_place);
    $row++;
    
    $sheet->mergeCells('B' . $row . ':C' . $row);
    $sheet->setCellValue('B' . $row, 'المشرف المباشر');
    $sheet->mergeCells('D' . $row . ':E' . $row);
    $sheet->setCellValue('D' . $row, $supervisor_name);
    $sheet->setCellValue('G' . $row, $signature_title);
    $row++;
    
    $sheet->mergeCells('B' . $row . ':C' . $row);
    $sheet->setCellValue('B' . $row, 'جوال المشرف المباشر');
    $sheet->setCellValue('D' . $row, $supervisor_phone);
    $row++;
    
    $sheet->mergeCells('B' . $row . ':C' . $row);
    $sheet->setCellValue('B' . $row, 'التوقيع');
    $row++;
    
    $sheet->mergeCells('F' . $row . ':G' . $row);
    $sheet->setCellValue('F' . $row, $signature_name);
    
    // Auto-size columns
    foreach (range('A', 'I') as $col) {
        $sheet->getColumnDimension($col)->setAutoSize(true);
    }
    
    // Borders
    $styleArray = [
        'borders' => [
            'allBorders' => [
                'borderStyle' => Border::BORDER_THIN,
            ],
        ],
    ];
    $sheet->getStyle('A5:I' . ($row - 3))->applyFromArray($styleArray);
    
    // Output
    $filename = 'salary_sheet_' . date('Y-m-d') . '.xlsx';
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment;filename="' . $filename . '"');
    header('Cache-Control: max-age=0');
    
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;
    
} else {
    // PDF Export
    require_once 'vendor/tecnickcom/tcpdf/tcpdf.php';
    
    $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8');
    $pdf->SetCreator('Library System');
    $pdf->SetAuthor('Library');
    $pdf->SetTitle('كشف حساب التشغيل');
    
    $pdf->setRTL(true);
    $pdf->SetFont('aealarabiya', '', 12);
    $pdf->AddPage();
    
    $html = '
    <style>
        table { border-collapse: collapse; width: 100%; }
        th, td { border: 1px solid #000; padding: 5px; text-align: center; }
        th { background-color: #CCCCCC; font-weight: bold; }
        .total { background-color: #FFFF00; font-weight: bold; }
    </style>
    
    <h2 style="text-align: center;">قائم تشغيل نادي</h2>
    <p style="text-align: center;">خلال الفترة من ' . $period_from_formatted . ' إلى ' . $period_to_formatted . ' م</p>
    
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
        <tbody>';
    
    $counter = 1;
    foreach ($students_data as $student) {
        $html .= '<tr>
            <td>' . $counter++ . '</td>
            <td>' . htmlspecialchars($student['name']) . '</td>
            <td>' . htmlspecialchars($student['academic_id']) . '</td>
            <td>' . htmlspecialchars($student['phone']) . '</td>
            <td>' . $student['hours'] . '</td>
            <td>' . $student['rate'] . '</td>
            <td>' . $student['amount'] . '</td>
            <td>' . htmlspecialchars($student['iban']) . '</td>
            <td>' . htmlspecialchars($student['bank']) . '</td>
        </tr>';
    }
    
    $html .= '<tr class="total">
            <td colspan="6">الإجمالي</td>
            <td>' . $total_amount . '</td>
            <td colspan="2">' . numberToArabicWords($total_amount) . '</td>
        </tr>
        </tbody>
    </table>
    
    <br><br>
    <table cellpadding="5">
        <tr>
            <td style="border: none;"><strong>مكان التشغيل:</strong></td>
            <td style="border: none;">' . htmlspecialchars($work_place) . '</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>المشرف المباشر:</strong></td>
            <td style="border: none;">' . htmlspecialchars($supervisor_name) . '</td>
        </tr>
        <tr>
            <td style="border: none;"><strong>جوال المشرف:</strong></td>
            <td style="border: none;">' . htmlspecialchars($supervisor_phone) . '</td>
        </tr>
    </table>
    
    <br><br>
    <p><strong>التوقيع:</strong> ' . htmlspecialchars($signature_name) . '</p>
    <p><strong>المسمى:</strong> ' . htmlspecialchars($signature_title) . '</p>
    ';
    
    $pdf->writeHTML($html, true, false, true, false, '');
    $pdf->Output('salary_sheet_' . date('Y-m-d') . '.pdf', 'D');
    exit;
}
?>
