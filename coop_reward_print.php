<?php
include_once 'auth.php';
requireAuth();
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>نموذج صرف المكافأة — طلاب التدريب التعاوني</title>
    <style>
        @page {
            size: A4 landscape;
            margin: 5mm 8mm 5mm 6mm;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Times New Roman', 'Arial', serif;
            direction: rtl;
            background: #eee;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        .page-wrapper {
            width: 297mm;
            min-height: 200mm;
            margin: 10px auto;
            background: #fff;
            padding: 8mm 10mm 6mm;
            box-shadow: 0 0 15px rgba(0,0,0,0.2);
        }

        /* ===== HEADER SECTION ===== */
        .doc-header {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            margin-bottom: 6px;
        }

        .header-right {
            text-align: right;
        }

        .header-right img {
            height: 65px;
        }

        .header-center {
            text-align: left;
            flex: 1;
            padding-left:4px;
        }

        .inst-name {
            font-size: 14pt;
            font-weight: bold;
            color: #8042FF;
            margin-bottom: 2px;
        }

        .dept-name {
            font-size: 11pt;
            color: #8042FF;
            font-weight: bold;
        }

        /* ===== TITLE SECTION ===== */
        .title-section {
            text-align: center;
            margin: 10px 0 8px;
            padding-bottom:16px;
        }

        .title-section h2 {
            font-size: 14pt;
            font-weight: bold;
            color: #8042FF;
            margin-bottom: 2px;
        }

        .title-section h3 {
            font-size: 13pt;
            font-weight: bold;
            color: #8042FF;
        }

        /* ===== MAIN TABLE ===== */
        .reward-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9pt;
            direction: rtl;
        }

        .reward-table th,
        .reward-table td {
            border: 1.5px solid #000;
            padding: 4px 3px;
            text-align: center;
            vertical-align: middle;
        }

        .reward-table thead th {
            background: #f0ebff;
            color: #333;
            font-weight: bold;
            font-size: 8.5pt;
        }
        
        
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 12pt;
            color: #1a5276;
            font-weight: bold;
        }

        .info-row span {
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .info-row .line {
            display: inline-block;
            width: 120px;
            border-bottom: 1px solid #1a5276;
        }

        /* Serial number column */
        .col-num { width: 25px; }

        /* Student name column */
        .col-name { width: 160px; font-size: 9pt; text-align: right; padding-right: 6px !important; }

        /* Absence column */
        .col-absence { width: 65px; font-size: 8pt; }

        /* Period columns */
        .col-period { width: 60px; font-size: 8pt; }

        /* Bank column */
        .col-bank { width: 55px; font-size: 8pt; }

        /* IBAN cells */
        .iban-cell {
            width: 16px;
            min-width: 16px;
            max-width: 16px;
            font-family: 'Courier New', monospace;
            font-size: 9pt;
            font-weight: bold;
            padding: 3px 1px;
            letter-spacing: 0;
        }

        .iban-header {
            font-size: 8pt;
            font-weight: bold;
        }

        /* ===== SIGNATURE SECTION ===== */
        .signature-section {
            margin-top: 25px;
            display: flex;
            justify-content: space-between;
            font-size: 11pt;
            color: #333;
            padding: 0 40px;
        }

        .sig-box {
            text-align: center;
            width: 40%;
        }

        .sig-box .sig-title {
            font-weight: bold;
            font-size: 12pt;
            margin-bottom: 5px;
        }

        .sig-box .sig-name {
            font-weight: bold;
            font-size: 11pt;
            margin-bottom: 30px;
        }

        .sig-box .sig-line {
            border-top: 1px solid #555;
            padding-top: 4px;
            font-size: 9pt;
            color: #666;
            margin-top: 40px;
        }

        /* ===== PRINT CONTROLS ===== */
        .print-controls {
            text-align: center;
            padding: 12px;
            background: #1b2d6b;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .print-controls button {
            margin: 4px 8px;
            padding: 10px 30px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-print { background: #2ebd70; color: #fff; }
        .btn-word { background: #2b579a; color: #fff; }
        .btn-close { background: #aaa; color: #fff; }

        @media print {
            .print-controls { display: none !important; }
            body { background: #fff; }
            .page-wrapper {
                margin: 0;
                box-shadow: none;
                padding: 4mm 6mm 3mm;
                width: 100%;
            }
        }

        .no-data {
            text-align: center;
            padding: 60px 20px;
            font-size: 16pt;
            color: #888;
        }
    </style>
</head>
<body>

<!-- Print Controls -->
<div class="print-controls">
    <button class="btn-print" onclick="window.print()">🖨️ طباعة</button>
    <button class="btn-word" onclick="exportToWord()">📄 تصدير Word</button>
    <button class="btn-close" onclick="window.close()">✖ إغلاق</button>
</div>

<div class="page-wrapper">

    <!-- Header -->
    <div class="doc-header">
        <div class="header-right">
            <img src="college.png" alt="شعار الهيئة الملكية">
        </div>
        <div class="header-center">
            <div class="inst-name">الهيئة الملكية للجبيل وينبع</div>
            <div class="inst-name" style="font-size: 12pt;">الهيئة الملكية بينبع</div>
            <div class="dept-name">إدارة تطوير الموارد البشرية</div>
        </div>
    </div>

    <!-- Titles -->
    <div class="title-section">
        <h2>برنامج التدريب التعاوني</h2>
        <h3>نموذج صرف المكافأة الشهرية</h3>
        <h3>لطلاب التدريب التعاوني</h3>
    </div>
    
      <div class="info-row">
        <span>إدارة: <span class="line"></span></span>
        <span>عن شهر: <span class="line"></span></span>
        <span>2026</span>
    </div>

    <!-- Table -->
    <div id="tableContainer">
        <div class="no-data" id="noDataMsg">جاري تحميل البيانات...</div>
    </div>

    <!-- Signatures -->
    <div class="signature-section" id="signatureSection" style="display: none;">
        <div class="sig-box">
            <div class="sig-title">مشرف التدريب</div>
            <div class="sig-name">نواف العتيبي</div>
  
        </div>
        <div class="sig-box">
            <div class="sig-title">مدير الإدارة</div>
            <div class="sig-name">د/ سوزان الدوبي</div>
      
        </div>
    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    var studentsJSON = sessionStorage.getItem('reward_students');
    var periodFrom   = sessionStorage.getItem('reward_period_from');
    var periodTo     = sessionStorage.getItem('reward_period_to');
    var periodFrom2  = sessionStorage.getItem('reward_period_from2') || '';
    var periodTo2    = sessionStorage.getItem('reward_period_to2') || '';

    if (!studentsJSON || !periodFrom || !periodTo) {
        document.getElementById('noDataMsg').textContent = '❌ لا توجد بيانات. ارجع لصفحة النموذج واختر الطلاب.';
        return;
    }

    var students = JSON.parse(studentsJSON);
    if (students.length === 0) {
        document.getElementById('noDataMsg').textContent = '❌ لم يتم اختيار أي طالب.';
        return;
    }

    // Check if we have a second period
    var hasPeriod2 = (periodFrom2 !== '' && periodTo2 !== '');

    // Build IBAN header (24 cells)
    var ibanHeaderCells = '';
    for (var i = 0; i < 24; i++) {
        ibanHeaderCells += '<th class="iban-cell"></th>';
    }

    var html = '<table class="reward-table">';
    html += '<thead>';
    // Top header row
    html += '<tr>';
    html += '<th class="col-num" rowspan="2">م</th>';
    html += '<th class="col-name" rowspan="2">اسم المتدرب</th>';
    html += '<th class="col-absence" rowspan="2">عدد أيام الغياب<br>(إن وجد)</th>';

    if (hasPeriod2) {
        html += '<th class="col-period" colspan="2">الفترة الأولى</th>';
        html += '<th class="col-period" colspan="2">الفترة الثانية</th>';
    } else {
        html += '<th class="col-period" colspan="2">الفترة المحتسبة للمكافأة<br>يوم / شهر</th>';
    }

    html += '<th class="col-bank" rowspan="2">اسم البنك</th>';
    
    // IBAN header - 24 cells in LTR order (right side of table)
    html += '<th class="iban-header" colspan="24">رقم الحساب (أيبان)</th>';
    html += '</tr>';

    // Sub-header row
    html += '<tr>';
    html += '<th class="col-period">من</th>';
    html += '<th class="col-period">إلى</th>';

    if (hasPeriod2) {
        html += '<th class="col-period">من</th>';
        html += '<th class="col-period">إلى</th>';
    }

    // IBAN sub-header cells
    html += ibanHeaderCells;
    html += '</tr>';
    html += '</thead>';
    html += '<tbody>';

    students.forEach(function(s, idx) {
        html += '<tr>';

        // Serial
        html += '<td class="col-num">' + (idx + 1) + '</td>';

        // Name (right-aligned)
        html += '<td class="col-name">' + s.name + '</td>';

        // Absence
        html += '<td class="col-absence">' + (s.absence || 'لا يوجد') + '</td>';

        // Period 1
        html += '<td class="col-period">' + periodFrom + '</td>';
        html += '<td class="col-period">' + periodTo + '</td>';

        // Period 2 (if exists)
        if (hasPeriod2) {
            html += '<td class="col-period">' + periodFrom2 + '</td>';
            html += '<td class="col-period">' + periodTo2 + '</td>';
        }

        // Bank
        html += '<td class="col-bank">' + (s.bank || '—') + '</td>';

        // IBAN cells (24 characters) - عكس الترتيب عشان يظهر صح في RTL
        var iban = (s.iban || '').replace(/\s/g, '').toUpperCase();
        while (iban.length < 24) iban += ' ';

        // عكس الآيبان عشان لما يترتب RTL يظهر صحيح LTR
        var ibanReversed = iban.split('').reverse().join('');
        
        for (var c = 0; c < 24; c++) {
            var ch = ibanReversed.charAt(c);
            html += '<td class="iban-cell">' + (ch.trim() || '') + '</td>';
        }

        html += '</tr>';
    });

    html += '</tbody></table>';

    document.getElementById('tableContainer').innerHTML = html;
    document.getElementById('signatureSection').style.display = 'flex';
});

function exportToWord() {
    var content = document.querySelector('.page-wrapper').innerHTML;
    var styles = document.querySelector('style').innerHTML;

    var htmlContent = '<!DOCTYPE html><html lang="ar" dir="rtl"><head><meta charset="UTF-8"><style>' + styles + '</style></head><body style="font-family: Times New Roman, Arial, serif; direction: rtl;">' + content + '</body></html>';

    var blob = new Blob(['\ufeff' + htmlContent], { type: 'application/msword' });
    var url = URL.createObjectURL(blob);
    var a = document.createElement('a');
    a.href = url;
    a.download = 'كشف_مكافأة_التدريب_التعاوني.doc';
    document.body.appendChild(a);
    a.click();
    document.body.removeChild(a);
    URL.revokeObjectURL(url);
}
</script>

</body>
</html>
