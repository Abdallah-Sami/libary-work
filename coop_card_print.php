<?php
include 'db_connect.php';
include_once 'auth.php';
requireAuth();

if (!isset($_GET['id'])) {
    header('Location: manage_coop_students.php');
    exit;
}

$id = intval($_GET['id']);
$result = $conn->query("SELECT * FROM coop_students WHERE id=$id");
if ($result->num_rows == 0) {
    header('Location: manage_coop_students.php');
    exit;
}
$student = $result->fetch_assoc();

// Hijri date helper (approximate)
function toHijri($gregorianDate) {
    // Simple approximation — use server's date if no date available
    return '';
}

$today_g = date('Y/m/d');
?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <title>بطاقة طالب تدريب تعاوني - <?= htmlspecialchars($student['full_name']) ?></title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Arial', sans-serif;
            direction: rtl;
            background: #f5f5f5;
        }

        .page-wrapper {
            width: 210mm;
            min-height: 297mm;
            margin: 20px auto;
            background: #fff;
            padding: 15mm 18mm;
            box-shadow: 0 0 20px rgba(0,0,0,0.15);
        }

        /* ===== HEADER ===== */
        .doc-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 3px solid #1b2d6b;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .header-logo img {
            height: 80px;
        }

        .header-center {
            text-align: center;
            flex: 1;
            padding: 0 15px;
        }

        .header-center .main-title {
            font-size: 17pt;
            font-weight: bold;
            color: #1b2d6b;
            line-height: 1.5;
        }

        .header-center .sub-title {
            font-size: 11pt;
            color: #444;
            margin-top: 4px;
        }

        .header-ref {
            text-align: left;
            font-size: 9pt;
            color: #555;
            min-width: 100px;
        }

        .header-ref div { margin-bottom: 4px; }

        /* ===== DOCUMENT TITLE ===== */
        .doc-title-box {
            text-align: center;
            margin: 18px 0 22px;
        }

        .doc-title-box h2 {
            display: inline-block;
            font-size: 15pt;
            font-weight: bold;
            color: #fff;
            background: #1b2d6b;
            padding: 8px 40px;
            border-radius: 4px;
            letter-spacing: 0.5px;
        }

        /* ===== INFO TABLE ===== */
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 22px;
            font-size: 11pt;
        }

        .info-table td {
            padding: 9px 12px;
            border: 1px solid #c0c8e0;
            vertical-align: middle;
        }

        .info-table .label {
            background: #eef1f9;
            font-weight: bold;
            color: #1b2d6b;
            width: 25%;
            white-space: nowrap;
        }

        .info-table .value {
            color: #222;
            width: 25%;
        }

        /* ===== PARAGRAPH BODY ===== */
        .doc-body {
            font-size: 11pt;
            line-height: 2;
            color: #222;
            margin: 20px 0;
            text-align: justify;
        }

        .doc-body .highlight {
            font-weight: bold;
            color: #1b2d6b;
            border-bottom: 1px dashed #1b2d6b;
            padding: 0 3px;
        }

        /* ===== SIGNATURE SECTION ===== */
        .signature-section {
            display: flex;
            justify-content: space-between;
            margin-top: 50px;
        }

        .sig-box {
            text-align: center;
            width: 30%;
        }

        .sig-box .sig-title {
            font-size: 10pt;
            font-weight: bold;
            color: #1b2d6b;
            margin-bottom: 40px;
        }

        .sig-box .sig-line {
            border-top: 1.5px solid #333;
            margin: 0 10px;
            padding-top: 6px;
            font-size: 9pt;
            color: #555;
        }

        /* ===== FOOTER ===== */
        .doc-footer {
            margin-top: 30px;
            border-top: 2px solid #1b2d6b;
            padding-top: 8px;
            text-align: center;
            font-size: 8.5pt;
            color: #666;
        }

        /* ===== PRINT CONTROLS (hidden on print) ===== */
        .print-controls {
            text-align: center;
            padding: 15px;
            background: #1b2d6b;
        }

        .print-controls button {
            margin: 5px;
            padding: 10px 28px;
            font-size: 14px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: bold;
        }

        .btn-print { background: #2ebd70; color: #fff; }
        .btn-back  { background: #aaa; color: #fff; }

        @media print {
            .print-controls { display: none; }
            body { background: #fff; }
            .page-wrapper {
                margin: 0;
                box-shadow: none;
                padding: 10mm 15mm;
            }
        }
    </style>
</head>
<body>

<!-- Print Controls -->
<div class="print-controls no-print">
    <button class="btn-print" onclick="window.print()">🖨️ طباعة</button>
    <button class="btn-back" onclick="window.close()">✖ إغلاق</button>
</div>

<div class="page-wrapper">

    <!-- Header -->
    <div class="doc-header">
        <div class="header-logo">
            <img src="college.png" alt="شعار الهيئة الملكية">
        </div>
        <div class="header-center">
            <div class="main-title">الهيئة الملكية للجبيل وينبع</div>
            <div class="sub-title">كلية ينبع الصناعية — المكتبة الرئيسية</div>
        </div>
        <div class="header-ref">
            <div><strong>التاريخ:</strong> <?= $today_g ?></div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="doc-title-box">
        <h2>بيانات طالب التدريب التعاوني</h2>
    </div>

    <!-- Student Info Table -->
    <table class="info-table">
        <tr>
            <td class="label">الاسم الكامل</td>
            <td class="value"><?= htmlspecialchars($student['full_name']) ?></td>
            <td class="label">الرقم الأكاديمي</td>
            <td class="value"><?= htmlspecialchars($student['academic_id']) ?></td>
        </tr>
        <tr>
            <td class="label">القسم</td>
            <td class="value"><?= htmlspecialchars($student['department'] ?: '—') ?></td>
            <td class="label">التخصص</td>
            <td class="value"><?= htmlspecialchars($student['major'] ?: '—') ?></td>
        </tr>
        <tr>
            <td class="label">رقم الجوال</td>
            <td class="value"><?= htmlspecialchars($student['phone'] ?: '—') ?></td>
            <td class="label">البريد الإلكتروني</td>
            <td class="value"><?= htmlspecialchars($student['email'] ?: '—') ?></td>
        </tr>
        <tr>
            <td class="label">رقم الآيبان</td>
            <td class="value"><?= htmlspecialchars($student['iban'] ?: '—') ?></td>
            <td class="label">اسم البنك</td>
            <td class="value"><?= htmlspecialchars($student['bank_name'] ?: '—') ?></td>
        </tr>
    </table>

    <!-- Body Paragraph -->
    <div class="doc-body">
        يُفيد مركز المكتبة الرئيسية بكلية ينبع الصناعية بأن الطالب
        <span class="highlight"><?= htmlspecialchars($student['full_name']) ?></span>
        ذا الرقم الأكاديمي
        <span class="highlight"><?= htmlspecialchars($student['academic_id']) ?></span>
        من قسم
        <span class="highlight"><?= htmlspecialchars($student['department'] ?: '—') ?></span>
        &mdash; تخصص
        <span class="highlight"><?= htmlspecialchars($student['major'] ?: '—') ?></span>
        ، يقوم بإجراء تدريبه التعاوني لدى مركز المكتبة الرئيسية وفق البرنامج المعتمد،
        وذلك للتعريف والعمل بموجبه.
    </div>

    <!-- Signatures -->
    <div class="signature-section">
        <div class="sig-box">
            <div class="sig-title">الطالب</div>
            <div class="sig-line">التوقيع / الاسم</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">مشرف التدريب</div>
            <div class="sig-line">التوقيع / الاسم</div>
        </div>
        <div class="sig-box">
            <div class="sig-title">رئيس القسم</div>
            <div class="sig-line">التوقيع / الاسم</div>
        </div>
    </div>

    <!-- Footer -->
    <div class="doc-footer">
        كلية ينبع الصناعية — المكتبة الرئيسية &nbsp;|&nbsp;
        الهيئة الملكية للجبيل وينبع
    </div>

</div>

</body>
</html>
