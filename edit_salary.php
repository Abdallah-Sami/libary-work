<?php
include 'db_connect.php';

// Auto-add daily_hours column if not exists
$check = $conn->query("SHOW COLUMNS FROM student_workers LIKE 'daily_hours'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE student_workers ADD COLUMN daily_hours INT DEFAULT 2 AFTER hourly_rate");
}

include 'header.php';

$sheet_id = intval($_GET['id'] ?? 0);
if ($sheet_id <= 0) {
    die("<script>alert('كشف غير صالح!'); window.location='saved_sheets.php';</script>");
}

// Get sheet data
$sheet_result = $conn->query("SELECT * FROM salary_sheets WHERE id = $sheet_id");
if (!$sheet_result || $sheet_result->num_rows == 0) {
    die("<script>alert('الكشف غير موجود!'); window.location='saved_sheets.php';</script>");
}
$sheet = $sheet_result->fetch_assoc();

// Get saved items to pre-fill hours
$saved_items = [];
$items_result = $conn->query("SELECT * FROM salary_sheet_items WHERE sheet_id = $sheet_id");
if ($items_result) {
    while ($item = $items_result->fetch_assoc()) {
        $saved_items[$item['student_id']] = $item;
    }
}

// Get students separated by daily hours
$students_2h = $conn->query("SELECT * FROM student_workers WHERE is_active = 1 AND (daily_hours = 2 OR daily_hours IS NULL) ORDER BY full_name ASC");
$students_3h = $conn->query("SELECT * FROM student_workers WHERE is_active = 1 AND daily_hours = 3 ORDER BY full_name ASC");
?>

<div class="content">
    <h2 class="page-title" dir="rtl">✏️ تعديل كشف محفوظ</h2>

    <form action="save_salary.php" method="POST" class="form-container" dir="rtl">
        <input type="hidden" name="edit_sheet_id" value="<?= $sheet_id ?>">

        <h3 style="text-align: center; margin-bottom: 20px; color: #1b2d6b;">📋 معلومات الكشف</h3>

        <div class="form-group">
            <label>📅 الفترة من:</label>
            <input type="date" name="period_from" class="form-control" value="<?= $sheet['period_from'] ?>" required>
        </div>
        <div class="form-group">
            <label>📅 الفترة إلى:</label>
            <input type="date" name="period_to" class="form-control" value="<?= $sheet['period_to'] ?>" required>
        </div>
        <div class="form-group">
            <label>📍 مكان التشغيل:</label>
            <input type="text" name="work_place" class="form-control" value="<?= htmlspecialchars($sheet['work_place']) ?>" required>
        </div>
        <div class="form-group">
            <label>👤 المشرف المباشر:</label>
            <input type="text" name="supervisor_name" class="form-control" value="<?= htmlspecialchars($sheet['supervisor_name']) ?>" required>
        </div>
        <div class="form-group">
            <label>📱 جوال المشرف:</label>
            <input type="text" name="supervisor_phone" class="form-control" value="<?= htmlspecialchars($sheet['supervisor_phone']) ?>" required>
        </div>
        <div class="form-group">
            <label>🏢 المسؤول عن التوقيع:</label>
            <input type="text" name="signature_name" class="form-control" value="<?= htmlspecialchars($sheet['signature_name']) ?>">
        </div>
        <div class="form-group">
            <label>📋 مسمى المسؤول:</label>
            <input type="text" name="signature_title" class="form-control" value="<?= htmlspecialchars($sheet['signature_title']) ?>">
        </div>

        <hr style="margin: 30px 0;">

        <!-- طلاب الساعتين -->
        <h3 style="text-align: center; margin-bottom: 20px; color: #1b2d6b;">👥 طلاب الساعتين (2 ساعات يومياً)</h3>
        <div style="background: #f4f6fb; padding: 20px; border-radius: 10px; margin-bottom: 30px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #1b2d6b; color: white;">
                        <th style="padding: 10px; width: 50px;">✓</th>
                        <th style="padding: 10px;">الاسم</th>
                        <th style="padding: 10px; width: 120px;">الرقم الأكاديمي</th>
                        <th style="padding: 10px; width: 120px;">عدد الساعات</th>
                        <th style="padding: 10px; width: 100px;">أجر الساعة</th>
                        <th style="padding: 10px; width: 120px;">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students_2h && $students_2h->num_rows > 0): ?>
                        <?php while($student = $students_2h->fetch_assoc()):
                            $is_saved = isset($saved_items[$student['id']]);
                            $saved_hours = $is_saved ? $saved_items[$student['id']]['hours'] : '';
                            $saved_amount = $is_saved ? $saved_items[$student['id']]['amount'] : 0;
                        ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px; text-align: center;">
                                    <input type="checkbox" name="students[]" value="<?= $student['id'] ?>"
                                           class="student-checkbox" onchange="updateTotal()"
                                           <?= $is_saved ? 'checked' : '' ?>>
                                    <input type="hidden" name="daily_hours_<?= $student['id'] ?>" value="2">
                                </td>
                                <td style="padding: 10px;"><?= htmlspecialchars($student['full_name']) ?></td>
                                <td style="padding: 10px; text-align: center;"><?= htmlspecialchars($student['academic_id']) ?></td>
                                <td style="padding: 10px;">
                                    <input type="number" name="hours_<?= $student['id'] ?>"
                                           class="form-control hours-input" data-student="<?= $student['id'] ?>"
                                           data-rate="<?= $student['hourly_rate'] ?>"
                                           min="0" step="0.5" placeholder="0"
                                           value="<?= $saved_hours ?>"
                                           style="padding: 5px; width: 100%;"
                                           onchange="calculateAmount(this)">
                                </td>
                                <td style="padding: 10px; text-align: center;"><?= $student['hourly_rate'] ?></td>
                                <td style="padding: 10px; text-align: center;" id="amount_<?= $student['id'] ?>"><?= number_format($saved_amount, 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: #888;">لا يوجد طلاب في فئة الساعتين</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #e8f5e9; font-weight: bold;">
                        <td colspan="5" style="padding: 12px; text-align: right;">مجموع طلاب الساعتين:</td>
                        <td style="padding: 12px; text-align: center;" id="total_2h">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- طلاب ثلاث ساعات -->
        <h3 style="text-align: center; margin-bottom: 20px; color: #1b2d6b;">👥 طلاب ثلاث ساعات (3 ساعات يومياً)</h3>
        <div style="background: #f4f6fb; padding: 20px; border-radius: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #1b2d6b; color: white;">
                        <th style="padding: 10px; width: 50px;">✓</th>
                        <th style="padding: 10px;">الاسم</th>
                        <th style="padding: 10px; width: 120px;">الرقم الأكاديمي</th>
                        <th style="padding: 10px; width: 120px;">عدد الساعات</th>
                        <th style="padding: 10px; width: 100px;">أجر الساعة</th>
                        <th style="padding: 10px; width: 120px;">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students_3h && $students_3h->num_rows > 0): ?>
                        <?php while($student = $students_3h->fetch_assoc()):
                            $is_saved = isset($saved_items[$student['id']]);
                            $saved_hours = $is_saved ? $saved_items[$student['id']]['hours'] : '';
                            $saved_amount = $is_saved ? $saved_items[$student['id']]['amount'] : 0;
                        ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px; text-align: center;">
                                    <input type="checkbox" name="students[]" value="<?= $student['id'] ?>"
                                           class="student-checkbox" onchange="updateTotal()"
                                           <?= $is_saved ? 'checked' : '' ?>>
                                    <input type="hidden" name="daily_hours_<?= $student['id'] ?>" value="3">
                                </td>
                                <td style="padding: 10px;"><?= htmlspecialchars($student['full_name']) ?></td>
                                <td style="padding: 10px; text-align: center;"><?= htmlspecialchars($student['academic_id']) ?></td>
                                <td style="padding: 10px;">
                                    <input type="number" name="hours_<?= $student['id'] ?>"
                                           class="form-control hours-input" data-student="<?= $student['id'] ?>"
                                           data-rate="<?= $student['hourly_rate'] ?>"
                                           min="0" step="0.5" placeholder="0"
                                           value="<?= $saved_hours ?>"
                                           style="padding: 5px; width: 100%;"
                                           onchange="calculateAmount(this)">
                                </td>
                                <td style="padding: 10px; text-align: center;"><?= $student['hourly_rate'] ?></td>
                                <td style="padding: 10px; text-align: center;" id="amount_<?= $student['id'] ?>"><?= number_format($saved_amount, 2) ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" style="text-align: center; padding: 20px; color: #888;">لا يوجد طلاب في فئة ثلاث ساعات</td></tr>
                    <?php endif; ?>
                </tbody>
                <tfoot>
                    <tr style="background: #e8f5e9; font-weight: bold;">
                        <td colspan="5" style="padding: 12px; text-align: right;">مجموع طلاب ثلاث ساعات:</td>
                        <td style="padding: 12px; text-align: center;" id="total_3h">0</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div style="background: #1b2d6b; color: white; padding: 15px; border-radius: 10px; margin-top: 20px; text-align: center; font-size: 18px; font-weight: bold;">
            المجموع الكلي: <span id="total_amount">0</span> ريال
        </div>

        <div class="action-buttons" style="margin-top: 30px;">
            <button type="submit" class="btn btn-primary" style="padding: 12px 40px; font-size: 16px;">
                💾 حفظ التعديلات
            </button>
            <a href="saved_sheets.php" class="btn btn-secondary">⬅ إلغاء</a>
        </div>
    </form>
</div>

<script>
function calculateAmount(input) {
    const studentId = input.dataset.student;
    const hours = parseFloat(input.value) || 0;
    const rate = parseFloat(input.dataset.rate) || 0;
    const amount = hours * rate;
    document.getElementById('amount_' + studentId).textContent = amount.toFixed(2);
    updateTotal();
}

function updateTotal() {
    let total2h = 0, total3h = 0;
    document.querySelectorAll('.student-checkbox:checked').forEach(function(cb) {
        const studentId = cb.value;
        const amount = parseFloat(document.getElementById('amount_' + studentId).textContent) || 0;
        const dh = cb.closest('td').querySelector('input[type="hidden"]').value;
        if (dh === '2') total2h += amount; else total3h += amount;
    });
    document.getElementById('total_2h').textContent = total2h.toFixed(2);
    document.getElementById('total_3h').textContent = total3h.toFixed(2);
    document.getElementById('total_amount').textContent = (total2h + total3h).toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    updateTotal();
});
</script>

<?php include 'footer.php'; ?>
