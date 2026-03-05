<?php

// Bank list used across student management forms
function getBankList() {
    return ['الراجحي','الأهلي','الرياض','الإنماء','البلاد','سامبا','ساب','الجزيرة','الفرنسي'];
}

// Render bank <option> tags with selected state
function bankOptions($selected = '') {
    $html = '<option value="">-- اختر البنك --</option>';
    foreach (getBankList() as $bank) {
        $sel = ($selected === $bank) ? ' selected' : '';
        $html .= '<option value="' . $bank . '"' . $sel . '>' . $bank . '</option>';
    }
    return $html;
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
    if ($thou > 0) { $result .= $thousands[$thou] . ' '; $number %= 1000; }
    $hund = floor($number / 100);
    if ($hund > 0) { $result .= $hundreds[$hund] . ' و'; $number %= 100; }
    $ten = floor($number / 10);
    if ($ten > 1) { $result .= $tens[$ten] . ' و'; $number %= 10; }
    if ($number > 0) { $result .= $ones[$number]; }
    return trim($result, ' و') . ' ريال سعودي';
}

// Generate Excel XML rows for salary sheets
function generateExcelRows($students, &$counter, $nameKey = 'name') {
    $output = '';
    foreach ($students as $s) {
        $name = htmlspecialchars($s[$nameKey] ?? $s['name'] ?? '');
        $output .= '<Row>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $counter++ . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . $name . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['academic_id']) . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['phone']) . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $s['hours'] . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . ($s['rate'] ?? $s['hourly_rate']) . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="Number">' . $s['amount'] . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['iban'] ?? '') . '</Data></Cell>';
        $output .= '<Cell ss:StyleID="tableCell"><Data ss:Type="String">' . htmlspecialchars($s['bank'] ?? $s['bank_name'] ?? '') . '</Data></Cell>';
        $output .= '</Row>';
    }
    return $output;
}

// Output Excel XML styles used by salary exports
function excelXmlStyles() {
    return '<Styles>'
        . '<Style ss:ID="header"><Font ss:Bold="1" ss:Size="14"/><Alignment ss:Horizontal="Center"/></Style>'
        . '<Style ss:ID="subheader"><Alignment ss:Horizontal="Center"/></Style>'
        . '<Style ss:ID="sectionTitle"><Font ss:Bold="1" ss:Size="12" ss:Color="#1B2D6B"/><Alignment ss:Horizontal="Center"/><Interior ss:Color="#E8F5E9" ss:Pattern="Solid"/></Style>'
        . '<Style ss:ID="tableHeader"><Font ss:Bold="1"/><Alignment ss:Horizontal="Center"/><Interior ss:Color="#CCCCCC" ss:Pattern="Solid"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>'
        . '<Style ss:ID="tableCell"><Alignment ss:Horizontal="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>'
        . '<Style ss:ID="totalRow"><Font ss:Bold="1"/><Interior ss:Color="#FFFF00" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>'
        . '<Style ss:ID="subtotalRow"><Font ss:Bold="1"/><Interior ss:Color="#E8F5E9" ss:Pattern="Solid"/><Alignment ss:Horizontal="Center"/><Borders><Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1"/><Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1"/></Borders></Style>'
        . '<Style ss:ID="footerLabel"><Font ss:Bold="1"/></Style>'
        . '</Styles>';
}

// Output Excel XML column widths for salary sheets
function excelXmlColumns() {
    return '<Column ss:Width="40"/><Column ss:Width="150"/><Column ss:Width="100"/><Column ss:Width="100"/>'
        . '<Column ss:Width="80"/><Column ss:Width="80"/><Column ss:Width="80"/><Column ss:Width="200"/><Column ss:Width="100"/>';
}

// Output Excel XML header rows (title + period)
function excelXmlHeader($periodFrom, $periodTo) {
    $output = '<Row ss:Height="25"><Cell ss:MergeAcross="8" ss:StyleID="header"><Data ss:Type="String">قائم تشغيل نادي</Data></Cell></Row>';
    $output .= '<Row><Cell ss:MergeAcross="8" ss:StyleID="subheader"><Data ss:Type="String">خلال الفترة من ' . $periodFrom . ' إلى ' . $periodTo . ' م</Data></Cell></Row>';
    $output .= '<Row/>';
    return $output;
}

// Output Excel XML table headers row
function excelXmlTableHeaders() {
    $headers = ['م', 'الاسم', 'الرقم الاكاديمي', 'رقم الجوال', 'عدد الساعات', 'اجر الساعه', 'المبلغ', 'رقم الايبان', 'البنك'];
    $output = '<Row>';
    foreach ($headers as $h) {
        $output .= '<Cell ss:StyleID="tableHeader"><Data ss:Type="String">' . $h . '</Data></Cell>';
    }
    $output .= '</Row>';
    return $output;
}

// Output Excel XML section (title + headers + rows + subtotal)
function excelXmlSection($title, $students, $subtotal, $nameKey = 'name') {
    $output = '<Row><Cell ss:MergeAcross="8" ss:StyleID="sectionTitle"><Data ss:Type="String">' . $title . '</Data></Cell></Row>';
    $output .= excelXmlTableHeaders();
    $c = 1;
    $output .= generateExcelRows($students, $c, $nameKey);
    $output .= '<Row><Cell ss:MergeAcross="5" ss:StyleID="subtotalRow"><Data ss:Type="String">مجموع ' . $title . '</Data></Cell>';
    $output .= '<Cell ss:StyleID="subtotalRow"><Data ss:Type="Number">' . $subtotal . '</Data></Cell>';
    $output .= '<Cell ss:MergeAcross="1" ss:StyleID="subtotalRow"><Data ss:Type="String">' . numberToArabicWords($subtotal) . '</Data></Cell></Row>';
    $output .= '<Row/>';
    return $output;
}

// Output Excel XML grand total row
function excelXmlGrandTotal($total) {
    return '<Row><Cell ss:MergeAcross="5" ss:StyleID="totalRow"><Data ss:Type="String">الإجمالي الكلي</Data></Cell>'
        . '<Cell ss:StyleID="totalRow"><Data ss:Type="Number">' . $total . '</Data></Cell>'
        . '<Cell ss:MergeAcross="1" ss:StyleID="totalRow"><Data ss:Type="String">' . numberToArabicWords($total) . '</Data></Cell></Row>';
}

// Output Excel XML footer rows
function excelXmlFooter($workPlace, $supervisorName, $supervisorPhone, $signatureTitle, $signatureName) {
    $output = '<Row/><Row/>';
    $output .= '<Row><Cell/><Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">مكان التشغيل</Data></Cell><Cell ss:MergeAcross="5"><Data ss:Type="String">' . htmlspecialchars($workPlace) . '</Data></Cell></Row>';
    $output .= '<Row><Cell/><Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">المشرف المباشر</Data></Cell><Cell ss:MergeAcross="2"><Data ss:Type="String">' . htmlspecialchars($supervisorName) . '</Data></Cell><Cell ss:MergeAcross="2"><Data ss:Type="String">' . htmlspecialchars($signatureTitle) . '</Data></Cell></Row>';
    $output .= '<Row><Cell/><Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">جوال المشرف المباشر</Data></Cell><Cell><Data ss:Type="String">' . htmlspecialchars($supervisorPhone) . '</Data></Cell></Row>';
    $output .= '<Row><Cell/><Cell ss:MergeAcross="1" ss:StyleID="footerLabel"><Data ss:Type="String">التوقيع</Data></Cell></Row>';
    return $output;
}

// Collect student salary data from POST
function collectSalaryStudents($conn, $selectedStudents) {
    $students_2h = [];
    $students_3h = [];
    $total_2h = 0;
    $total_3h = 0;

    foreach ($selectedStudents as $student_id) {
        $student_id = intval($student_id);
        $hours = floatval($_POST['hours_' . $student_id] ?? 0);
        $daily_hours = intval($_POST['daily_hours_' . $student_id] ?? 2);

        if ($hours > 0) {
            $result = $conn->query("SELECT * FROM student_workers WHERE id = $student_id");
            if ($result && $result->num_rows > 0) {
                $student = $result->fetch_assoc();
                $amount = $hours * $student['hourly_rate'];
                $data = [
                    'student_id' => $student_id,
                    'name' => $student['full_name'],
                    'academic_id' => $student['academic_id'],
                    'phone' => $student['phone'],
                    'hours' => $hours,
                    'rate' => $student['hourly_rate'],
                    'hourly_rate' => $student['hourly_rate'],
                    'amount' => $amount,
                    'iban' => $student['iban'] ?? '',
                    'bank' => $student['bank_name'] ?? '',
                    'bank_name' => $student['bank_name'] ?? '',
                    'daily_hours' => $daily_hours
                ];
                if ($daily_hours == 3) { $students_3h[] = $data; $total_3h += $amount; }
                else { $students_2h[] = $data; $total_2h += $amount; }
            }
        }
    }
    return compact('students_2h', 'students_3h', 'total_2h', 'total_3h');
}

// Ensure salary_sheets tables exist
function ensureSalaryTables($conn) {
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
}
