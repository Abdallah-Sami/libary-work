<?php
include 'db_connect.php';

// Auto-add daily_hours column if not exists
$check = $conn->query("SHOW COLUMNS FROM student_workers LIKE 'daily_hours'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE student_workers ADD COLUMN daily_hours INT DEFAULT 2 AFTER hourly_rate");
}

include 'header.php';

// Get students separated by daily hours
$students_2h = $conn->query("SELECT * FROM student_workers WHERE is_active = 1 AND (daily_hours = 2 OR daily_hours IS NULL) ORDER BY full_name ASC");
$students_3h = $conn->query("SELECT * FROM student_workers WHERE is_active = 1 AND daily_hours = 3 ORDER BY full_name ASC");
?>

<div class="content">
    <h2 class="page-title" dir="rtl">💰 كشف حساب التشغيل الطلابي</h2>

    <form action="salary_export_improved.php" method="POST" class="form-container" dir="rtl">

        <h3 style="text-align: center; margin-bottom: 20px; color: #1b2d6b;">
            📋 معلومات الكشف
        </h3>

        <div class="form-group">
            <label>📅 الفترة من:</label>
            <input type="date" name="period_from" class="form-control" required>
        </div>

        <div class="form-group">
            <label>📅 الفترة إلى:</label>
            <input type="date" name="period_to" class="form-control" required>
        </div>

        <div class="form-group">
            <label>📍 مكان التشغيل:</label>
            <input type="text" name="work_place" class="form-control" value="قسم مصادر التعلم / المكتبات" required>
        </div>

        <div class="form-group">
            <label>👤 المشرف المباشر:</label>
            <input type="text" name="supervisor_name" class="form-control" placeholder="مثال: عيد جميعان الرفاعي" required>
        </div>

        <div class="form-group">
            <label>📱 جوال المشرف:</label>
            <input type="text" name="supervisor_phone" class="form-control" placeholder="مثال: 0501234567" required>
        </div>

        <div class="form-group">
            <label>🏢 المسؤول عن التوقيع:</label>
            <input type="text" name="signature_name" class="form-control" placeholder="مثال: حاتم بن حامد الحربي">
        </div>

        <div class="form-group">
            <label>📋 مسمى المسؤول:</label>
            <input type="text" name="signature_title" class="form-control" placeholder="مثال: مشرف الأنشطة الطلابية">
        </div>

        <hr style="margin: 30px 0;">

        <!-- ===== طلاب الساعتين ===== -->
        <h3 style="text-align: center; margin-bottom: 20px; color: #1b2d6b;">
            👥 طلاب الساعتين (2 ساعات يومياً)
        </h3>

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
                        <?php while($student = $students_2h->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px; text-align: center;">
                                    <input type="checkbox" name="students[]" value="<?= $student['id'] ?>"
                                           class="student-checkbox" onchange="updateTotal()">
                                    <input type="hidden" name="daily_hours_<?= $student['id'] ?>" value="2">
                                </td>
                                <td style="padding: 10px;"><?= htmlspecialchars($student['full_name']) ?></td>
                                <td style="padding: 10px; text-align: center;"><?= htmlspecialchars($student['academic_id']) ?></td>
                                <td style="padding: 10px;">
                                    <input type="number" name="hours_<?= $student['id'] ?>"
                                           class="form-control hours-input" data-student="<?= $student['id'] ?>"
                                           data-rate="<?= $student['hourly_rate'] ?>"
                                           min="0" step="0.5" placeholder="0"
                                           style="padding: 5px; width: 100%;"
                                           onchange="calculateAmount(this)">
                                </td>
                                <td style="padding: 10px; text-align: center;" class="rate-cell"><?= $student['hourly_rate'] ?></td>
                                <td style="padding: 10px; text-align: center;" class="amount-cell" id="amount_<?= $student['id'] ?>">0</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #888;">لا يوجد طلاب في فئة الساعتين</td>
                        </tr>
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

        <!-- ===== طلاب ثلاث ساعات ===== -->
        <h3 style="text-align: center; margin-bottom: 20px; color: #1b2d6b;">
            👥 طلاب ثلاث ساعات (3 ساعات يومياً)
        </h3>

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
                        <?php while($student = $students_3h->fetch_assoc()): ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 10px; text-align: center;">
                                    <input type="checkbox" name="students[]" value="<?= $student['id'] ?>"
                                           class="student-checkbox" onchange="updateTotal()">
                                    <input type="hidden" name="daily_hours_<?= $student['id'] ?>" value="3">
                                </td>
                                <td style="padding: 10px;"><?= htmlspecialchars($student['full_name']) ?></td>
                                <td style="padding: 10px; text-align: center;"><?= htmlspecialchars($student['academic_id']) ?></td>
                                <td style="padding: 10px;">
                                    <input type="number" name="hours_<?= $student['id'] ?>"
                                           class="form-control hours-input" data-student="<?= $student['id'] ?>"
                                           data-rate="<?= $student['hourly_rate'] ?>"
                                           min="0" step="0.5" placeholder="0"
                                           style="padding: 5px; width: 100%;"
                                           onchange="calculateAmount(this)">
                                </td>
                                <td style="padding: 10px; text-align: center;" class="rate-cell"><?= $student['hourly_rate'] ?></td>
                                <td style="padding: 10px; text-align: center;" class="amount-cell" id="amount_<?= $student['id'] ?>">0</td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 20px; color: #888;">لا يوجد طلاب في فئة ثلاث ساعات</td>
                        </tr>
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

        <!-- المجموع الكلي -->
        <div style="background: #1b2d6b; color: white; padding: 15px; border-radius: 10px; margin-top: 20px; text-align: center; font-size: 18px; font-weight: bold;">
            المجموع الكلي: <span id="total_amount">0</span> ريال
        </div>

        <div class="action-buttons" style="margin-top: 30px;">
            <button type="submit" name="export_type" value="excel" class="btn btn-success">
                📊 تصدير Excel
            </button>
            <button type="submit" name="export_type" value="pdf" class="btn btn-danger">
                📄 تصدير PDF
            </button>
            <a href="index.php" class="btn btn-secondary">⬅ العودة</a>
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
    let total2h = 0;
    let total3h = 0;

    const checkboxes = document.querySelectorAll('.student-checkbox:checked');

    checkboxes.forEach(function(checkbox) {
        const studentId = checkbox.value;
        const amountText = document.getElementById('amount_' + studentId).textContent;
        const amount = parseFloat(amountText) || 0;
        const dailyHours = checkbox.closest('td').querySelector('input[type="hidden"]').value;

        if (dailyHours === '2') {
            total2h += amount;
        } else {
            total3h += amount;
        }
    });

    document.getElementById('total_2h').textContent = total2h.toFixed(2);
    document.getElementById('total_3h').textContent = total3h.toFixed(2);
    document.getElementById('total_amount').textContent = (total2h + total3h).toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    const inputs = document.querySelectorAll('.hours-input');
    inputs.forEach(function(input) {
        input.addEventListener('change', function() {
            calculateAmount(this);
        });
    });
});
</script>

<?php include 'footer.php'; ?>
