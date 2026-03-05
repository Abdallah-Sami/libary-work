<?php
include 'db_connect.php';
require_once 'helpers.php';
ensureSalaryTables($conn);

// Handle delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM salary_sheets WHERE id = $id");
    echo "<script>alert('✅ تم حذف الكشف بنجاح!'); window.location='saved_sheets.php';</script>";
}

include 'header.php';

// Get all saved sheets
$sheets = $conn->query("SELECT * FROM salary_sheets ORDER BY created_at DESC");
$total_sheets = $sheets ? $sheets->num_rows : 0;
?>

<div class="content">
    <h2 class="page-title" dir="rtl">📂 الكشوف المحفوظة</h2>

    <div class="stats-box" dir="rtl">
        ✅ إجمالي الكشوف المحفوظة: <?= $total_sheets ?>
    </div>

    <div style="text-align: center; margin-bottom: 20px;">
        <a href="salary_sheet.php" class="btn btn-primary" style="padding: 12px 30px; font-size: 16px;">
            ➕ إنشاء كشف جديد
        </a>
    </div>

    <?php if ($total_sheets > 0): ?>
    <table dir="rtl">
        <thead>
            <tr>
                <th>م</th>
                <th>الفترة</th>
                <th>مكان التشغيل</th>
                <th>المشرف</th>
                <th>الإجمالي</th>
                <th>تاريخ الحفظ</th>
                <th class="no-print">الإجراءات</th>
            </tr>
        </thead>
        <tbody>
            <?php $counter = 1; ?>
            <?php while($sheet = $sheets->fetch_assoc()): ?>
                <tr>
                    <td><?= $counter++ ?></td>
                    <td>
                        <?= date('d/m/Y', strtotime($sheet['period_from'])) ?>
                        —
                        <?= date('d/m/Y', strtotime($sheet['period_to'])) ?>
                    </td>
                    <td><?= htmlspecialchars($sheet['work_place']) ?></td>
                    <td><?= htmlspecialchars($sheet['supervisor_name']) ?></td>
                    <td style="font-weight: bold; color: #1b2d6b;"><?= number_format($sheet['total_amount'], 2) ?> ريال</td>
                    <td><?= date('d/m/Y h:i A', strtotime($sheet['created_at'])) ?></td>
                    <td class="no-print">
                        <a href="view_salary.php?id=<?= $sheet['id'] ?>" class="btn btn-primary" style="padding: 6px 12px; margin: 2px;">
                            👁️ عرض
                        </a>
                        <a href="view_salary.php?id=<?= $sheet['id'] ?>&action=print" target="_blank" class="btn btn-success" style="padding: 6px 12px; margin: 2px;">
                            🖨️ طباعة
                        </a>
                        <a href="edit_salary.php?id=<?= $sheet['id'] ?>" class="btn btn-warning" style="padding: 6px 12px; margin: 2px;">
                            ✏️ تعديل
                        </a>
                        <a href="view_salary.php?id=<?= $sheet['id'] ?>&action=excel" class="btn btn-success" style="padding: 6px 12px; margin: 2px; background: #217346;">
                            📊 Excel
                        </a>
                        <a href="?delete=<?= $sheet['id'] ?>" class="btn btn-danger" style="padding: 6px 12px; margin: 2px;"
                           onclick="return confirm('هل أنت متأكد من حذف هذا الكشف؟')">
                            🗑️ حذف
                        </a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
    <?php else: ?>
    <div style="text-align: center; padding: 60px 20px; color: #888; font-size: 16pt;" dir="rtl">
        📂 لا توجد كشوف محفوظة. ابدأ بإنشاء كشف جديد!
    </div>
    <?php endif; ?>
</div>

<?php include 'footer.php'; ?>
