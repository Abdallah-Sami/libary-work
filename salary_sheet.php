<?php
include 'db_connect.php';
include 'header.php';

// Get all active students
$students = $conn->query("SELECT * FROM student_workers WHERE is_active = 1 ORDER BY full_name ASC");
?>

<div class="content">
    <h2 class="page-title" dir="rtl">💰 كشف حساب التشغيل الطلابي</h2>

    <form action="salary_export_improved.php" method="POST" class="form-container" dir="rtl">
        
        <h3 style="text-align: center; margin-bottom: 20px; color: #667eea;">
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

        <h3 style="text-align: center; margin-bottom: 20px; color: #667eea;">
            👥 اختيار الطلاب وعدد الساعات
        </h3>

        <div style="background: #f8f9fa; padding: 20px; border-radius: 10px;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #667eea; color: white;">
                        <th style="padding: 10px; width: 50px;">✓</th>
                        <th style="padding: 10px;">الاسم</th>
                        <th style="padding: 10px; width: 120px;">الرقم الأكاديمي</th>
                        <th style="padding: 10px; width: 120px;">عدد الساعات</th>
                        <th style="padding: 10px; width: 100px;">أجر الساعة</th>
                        <th style="padding: 10px; width: 120px;">المبلغ</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $counter = 0; ?>
                    <?php while($student = $students->fetch_assoc()): ?>
                        <?php $counter++; ?>
                        <tr style="border-bottom: 1px solid #ddd;">
                            <td style="padding: 10px; text-align: center;">
                                <input type="checkbox" name="students[]" value="<?= $student['id'] ?>" 
                                       class="student-checkbox" onchange="updateTotal()">
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
                </tbody>
                <tfoot>
                    <tr style="background: #667eea; color: white; font-weight: bold;">
                        <td colspan="5" style="padding: 15px; text-align: right;">المجموع الكلي:</td>
                        <td style="padding: 15px; text-align: center;" id="total_amount">0</td>
                    </tr>
                </tfoot>
            </table>
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
    let total = 0;
    const checkboxes = document.querySelectorAll('.student-checkbox:checked');
    
    checkboxes.forEach(function(checkbox) {
        const studentId = checkbox.value;
        const amountText = document.getElementById('amount_' + studentId).textContent;
        const amount = parseFloat(amountText) || 0;
        total += amount;
    });
    
    document.getElementById('total_amount').textContent = total.toFixed(2);
}

// Auto-calculate all amounts when page loads
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
