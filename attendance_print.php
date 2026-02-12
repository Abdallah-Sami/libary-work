<?php
$student_name = $_POST['student_name'] ?? '';
$student_id   = $_POST['student_id'] ?? '';
$month_name   = $_POST['month_name'] ?? '';
$month_number = intval($_POST['month_number'] ?? 0);
$year_g       = intval($_POST['year_g'] ?? 0);
$year_h       = $_POST['year_h'] ?? '';

if ($month_number < 1 || $month_number > 12) {
    die("<script>alert('خطأ: رقم الشهر غير صحيح'); window.close();</script>");
}

if ($year_g < 1900) {
    die("<script>alert('خطأ: السنة غير صحيحة'); window.close();</script>");
}

$days_in_month = cal_days_in_month(CAL_GREGORIAN, $month_number, $year_g);
$days_ar = ["الأحد", "الاثنين", "الثلاثاء", "الأربعاء", "الخميس", "الجمعة", "السبت"];
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>كشف حضور - <?php echo htmlspecialchars($student_name); ?></title>
    <style>
        body {
            font-family: 'Tahoma', 'Arial', sans-serif;
            direction: rtl;
            text-align: right;
            margin: 15px;
            background: #f4f6fb;
        }

        .container {
            max-width: 210mm;
            margin: 0 auto;
            padding: 8mm;
            background: #fff;
            border: 2px solid #d7e5ff;
            border-radius: 10px;
        }

        .header {
            position: relative;
            text-align: center;
            margin-bottom: 10px;
            line-height: 1.5;
            color: #1b2d6b;
            min-height: 80px;
        }

        .print-logo {
            position: absolute;
            top: 0;
            right: 0;
            width: 85px;
            height: auto;
        }

        .header h3 {
            margin: 2px 0;
            font-size: 13px;
        }

        .header h2 {
            margin: 6px 0;
            font-size: 15px;
            font-weight: bold;
        }

        .info-section {
            margin: 10px 0;
            padding: 6px 8px;
            background: #f4f6fb;
            border-radius: 5px;
            font-size: 13px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 8px 0;
        }

        th, td {
            border: 1.5px solid #000;
            padding: 4px 3px;
            text-align: center;
            font-size: 11px;
        }

        th {
            background: #e3ecff;
            font-weight: bold;
            font-size: 11px;
        }

        .total-section {
            margin: 8px 0;
            padding: 6px 8px;
            background: #fff3cd;
            border-radius: 5px;
            font-size: 12px;
        }

        .signature-section {
            margin-top: 10px;
            padding: 8px;
            border: 1px solid #d7e5ff;
            border-radius: 5px;
            background: #f9fbff;
            font-size: 12px;
        }

        .signature-section h4 {
            font-size: 12px;
            margin-bottom: 6px;
        }

        .signature-line {
            border-bottom: 1px solid #000;
            display: inline-block;
            min-width: 180px;
            margin: 0 10px;
        }

        .signature-section div {
            margin: 6px 0;
        }

        @media print {
            body {
                margin: 0;
                padding: 0;
                background: white;
            }

            .no-print {
                display: none !important;
            }

            @page {
                size: A4;
                margin: 6mm;
            }

            html, body {
                height: 100%;
            }

            .container {
                page-break-inside: avoid !important;
                border: none;
                padding: 4mm;
                max-width: 100%;
            }

            .header {
                margin-bottom: 6px;
                line-height: 1.3;
            }

            .print-logo {
                width: 70px;
            }

            .header h3 {
                font-size: 11px;
                margin: 1px 0;
            }

            .header h2 {
                font-size: 13px;
                margin: 3px 0;
            }

            .info-section {
                margin: 6px 0;
                padding: 3px 6px;
                font-size: 11px;
            }

            table {
                margin: 5px 0;
                font-size: 9px;
            }

            th, td {
                padding: 2px 1px;
                font-size: 9px;
            }

            .total-section {
                margin: 5px 0;
                padding: 3px 6px;
                font-size: 10px;
            }

            .signature-section {
                margin-top: 6px;
                padding: 5px 8px;
                font-size: 10px;
            }

            .signature-section h4 {
                font-size: 10px;
                margin-bottom: 4px;
            }

            .signature-section div {
                margin: 3px 0;
            }

            .signature-line {
                min-width: 150px;
            }
        }

        .print-btn {
            background: #1b2d6b;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 5px;
        }

        .print-btn:hover {
            background: #142354;
        }

        .close-btn {
            background: #7b4dba;
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            margin: 10px 5px;
        }

        .close-btn:hover {
            background: #6a3fa5;
        }
    </style>
</head>
<body>

<div class="no-print" style="text-align: center; margin-bottom: 20px;">
    <button onclick="window.print()" class="print-btn">🖨️ طباعة الكشف</button>
    <button onclick="window.close()" class="close-btn">✖️ إغلاق</button>
</div>

<div class="container">
    <div class="header">
        <img src="college.png" alt="شعار الهيئة الملكية" class="print-logo">
        <h3>الهيئة الملكية بينبع</h3>
        <h3>الإدارة العامة للتعليم بالهيئة الملكية بينبع</h3>
        <h3>وكالة الكليات لشؤون الطلاب</h3>
        <h3>خدمات الطلاب</h3>

        <h2>كشف الحضور والانصراف لطلاب التشغيل</h2>
        <h3>بقسم: مصادر التعلم</h3>
        <h3>
            لشهر (<?php echo htmlspecialchars($month_name); ?>)
            لعام <?php echo $year_g; ?>م
            <?php if (!empty($year_h)) echo " ($year_h)"; ?>
        </h3>
    </div>

    <div class="info-section">
        <strong>اسم الطالب:</strong> <?php echo htmlspecialchars($student_name); ?>
        &nbsp;&nbsp;&nbsp;|&nbsp;&nbsp;&nbsp;
        <strong>الرقم الأكاديمي:</strong> <?php echo htmlspecialchars($student_id); ?>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 10%;">اليوم</th>
                <th style="width: 12%;">التاريخ</th>
                <th style="width: 15%;">الحضور</th>
                <th style="width: 15%;">الانصراف</th>
                <th style="width: 13%;">عدد الساعات</th>
                <th style="width: 35%;">التوقيع</th>
            </tr>
        </thead>
        <tbody>
            <?php
            for ($day = 1; $day <= $days_in_month; $day++) {
                $date_str = "$year_g-$month_number-$day";
                $weekday_num = date("w", strtotime($date_str));
                $weekday_name = $days_ar[$weekday_num];
            ?>
            <tr>
                <td><?php echo $weekday_name; ?></td>
                <td><?php echo "$day / $month_number / $year_g"; ?></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <div class="total-section">
        <strong>مجموع الساعات الإجمالي:</strong>
        <span class="signature-line" style="min-width: 150px;"></span>
    </div>

    <div class="signature-section">
        <h4>مشرف التشغيل في مكتبة كلية ينبع الصناعية</h4>

        <div>
            <strong>الاسم:</strong> <span class="signature-line"></span>
        </div>

        <div>
            <strong>التوقيع:</strong> <span class="signature-line"></span>
        </div>

        <div>
            <strong>التاريخ:</strong>
            <span class="signature-line" style="min-width: 60px;"></span> /
            <span class="signature-line" style="min-width: 60px;"></span> /
            <?php echo $year_g; ?>
        </div>
    </div>
</div>

</body>
</html>
