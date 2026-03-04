<?php 
include 'db_connect.php';
include 'header.php';

// Get all students from database
$students = $conn->query("SELECT id, full_name, academic_id FROM student_workers ORDER BY full_name ASC");
?>

<div class="content">
    <h2 class="page-title" dir="rtl">📝 إنشاء كشف حضور تشغيل طالب</h2>

    <div style="margin: 20px 0; padding: 15px; background: #d4edda; border-radius: 8px; text-align: right;" dir="rtl">
        <strong>💡 ملاحظة:</strong> إذا لم تجد الطالب في القائمة، 
        <a href="manage_students.php" style="color: #007bff; font-weight: bold;">اضغط هنا لإضافة طالب جديد</a>
    </div>

    <form action="attendance_print.php" method="POST" target="_blank" class="form-container" dir="rtl">
        
        <div class="form-group">
            <label for="student_select">👤 اختر الطالب: *</label>
            <select id="student_select" class="form-control" onchange="fillStudentData()" required>
                <option value="">-- اختر طالب من القائمة --</option>
                <?php while($student = $students->fetch_assoc()): ?>
                    <option value="<?= $student['id'] ?>" 
                            data-name="<?= htmlspecialchars($student['full_name']) ?>" 
                            data-id="<?= htmlspecialchars($student['academic_id']) ?>">
                        <?= htmlspecialchars($student['full_name']) ?> - <?= htmlspecialchars($student['academic_id']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>

        <div style="padding: 15px; background: #f4f6fb; border-radius: 8px; margin: 20px 0;">
            <h4 style="margin-bottom: 15px; color: #1b2d6b;">📋 بيانات الطالب المختار:</h4>
            
            <div class="form-group">
                <label for="student_name">👤 اسم الطالب:</label>
                <input type="text" id="student_name" name="student_name" class="form-control" 
                       placeholder="سيتم التعبئة تلقائياً" readonly required style="background: #e3ecff;">
            </div>

            <div class="form-group">
                <label for="student_id">🔢 الرقم الأكاديمي:</label>
                <input type="text" id="student_id" name="student_id" class="form-control" 
                       placeholder="سيتم التعبئة تلقائياً" readonly required style="background: #e3ecff;">
            </div>
        </div>

        <div class="form-group">
            <label for="month_name">📅 اسم الشهر:</label>
            <select id="month_name" name="month_name" class="form-control" required>
                <option value="">-- اختر الشهر --</option>
                <option value="يناير">يناير</option>
                <option value="فبراير">فبراير</option>
                <option value="مارس">مارس</option>
                <option value="أبريل">أبريل</option>
                <option value="مايو">مايو</option>
                <option value="يونيو">يونيو</option>
                <option value="يوليو">يوليو</option>
                <option value="أغسطس">أغسطس</option>
                <option value="سبتمبر">سبتمبر</option>
                <option value="أكتوبر">أكتوبر</option>
                <option value="نوفمبر">نوفمبر</option>
                <option value="ديسمبر">ديسمبر</option>
            </select>
        </div>

        <div class="form-group">
            <label for="month_number">🔢 رقم الشهر (1-12):</label>
            <select id="month_number" name="month_number" class="form-control" required>
                <option value="">-- اختر رقم الشهر --</option>
                <option value="1">1 - يناير</option>
                <option value="2">2 - فبراير</option>
                <option value="3">3 - مارس</option>
                <option value="4">4 - أبريل</option>
                <option value="5">5 - مايو</option>
                <option value="6">6 - يونيو</option>
                <option value="7">7 - يوليو</option>
                <option value="8">8 - أغسطس</option>
                <option value="9">9 - سبتمبر</option>
                <option value="10">10 - أكتوبر</option>
                <option value="11">11 - نوفمبر</option>
                <option value="12">12 - ديسمبر</option>
            </select>
        </div>

        <div class="form-group">
            <label for="year_g">📆 السنة الميلادية:</label>
            <input type="number" id="year_g" name="year_g" class="form-control" value="2025" min="2020" max="2030" required>
        </div>

        <div class="form-group">
            <label for="year_h">🌙 السنة الهجرية (اختياري):</label>
            <input type="text" id="year_h" name="year_h" class="form-control" placeholder="مثال: 1446 هـ">
        </div>

        <div class="form-group">
            <label for="daily_hours">⏰ عدد ساعات العمل اليومية:</label>
            <select id="daily_hours" name="daily_hours" class="form-control" required>
                <option value="">-- اختر عدد الساعات --</option>
                <option value="2">ساعتين (2)</option>
                <option value="3">ثلاث ساعات (3)</option>
            </select>
        </div>

        <div class="action-buttons">
            <button type="submit" class="btn btn-primary">🖨️ إنشاء وطباعة الكشف</button>
            <a href="manage_students.php" class="btn btn-success">➕ إدارة الطلاب</a>
            <a href="index.php" class="btn btn-secondary">⬅ العودة للرئيسية</a>
        </div>
    </form>

    <div style="margin: 30px 0; padding: 15px; background: #fff3cd; border-radius: 8px; text-align: right;" dir="rtl">
        <strong>📌 ملاحظات هامة:</strong>
        <ul style="margin: 10px 0 0 20px; text-align: right;">
            <li>اختر الطالب من القائمة المنسدلة - سيتم تعبئة البيانات تلقائياً</li>
            <li>سيتم إنشاء كشف حضور لجميع أيام الشهر المحدد</li>
            <li>يمكن طباعة الكشف مباشرة بعد إنشائه</li>
            <li>تأكد من صحة البيانات قبل الطباعة</li>
        </ul>
    </div>
</div>

<script>
// Auto-fill student data when selected
function fillStudentData() {
    const select = document.getElementById('student_select');
    const selectedOption = select.options[select.selectedIndex];
    
    if (selectedOption.value) {
        const studentName = selectedOption.getAttribute('data-name');
        const studentId = selectedOption.getAttribute('data-id');
        
        document.getElementById('student_name').value = studentName;
        document.getElementById('student_id').value = studentId;
    } else {
        document.getElementById('student_name').value = '';
        document.getElementById('student_id').value = '';
    }
}

// Sync month name with month number
document.getElementById('month_number').addEventListener('change', function() {
    const monthNames = ['', 'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو', 
                        'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر'];
    const monthNum = parseInt(this.value);
    if (monthNum >= 1 && monthNum <= 12) {
        document.getElementById('month_name').value = monthNames[monthNum];
    }
});

document.getElementById('month_name').addEventListener('change', function() {
    const monthMapping = {
        'يناير': 1, 'فبراير': 2, 'مارس': 3, 'أبريل': 4,
        'مايو': 5, 'يونيو': 6, 'يوليو': 7, 'أغسطس': 8,
        'سبتمبر': 9, 'أكتوبر': 10, 'نوفمبر': 11, 'ديسمبر': 12
    };
    const monthNum = monthMapping[this.value];
    if (monthNum) {
        document.getElementById('month_number').value = monthNum;
    }
});
</script>

<?php include 'footer.php'; ?>

