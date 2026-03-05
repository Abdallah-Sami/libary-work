<?php
include 'db_connect.php';
require_once 'helpers.php';

// Auto-add daily_hours column if not exists
$check = $conn->query("SHOW COLUMNS FROM student_workers LIKE 'daily_hours'");
if ($check && $check->num_rows == 0) {
    $conn->query("ALTER TABLE student_workers ADD COLUMN daily_hours INT DEFAULT 2 AFTER hourly_rate");
}

include 'header.php';

// Handle Add/Edit Student
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = $conn->real_escape_string($_POST['full_name']);
    $academic_id = $conn->real_escape_string($_POST['academic_id']);
    $department = $conn->real_escape_string($_POST['department']);
    $major = $conn->real_escape_string($_POST['major']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $email = $conn->real_escape_string($_POST['email']);
    $iban = $conn->real_escape_string($_POST['iban']);
    $bank_name = $conn->real_escape_string($_POST['bank_name']);
    $hourly_rate = floatval($_POST['hourly_rate']);
    $daily_hours = intval($_POST['daily_hours'] ?? 2);

    if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
        // Update existing student
        $student_id = intval($_POST['student_id']);
        $sql = "UPDATE student_workers SET
                full_name='$full_name',
                academic_id='$academic_id',
                department='$department',
                major='$major',
                phone='$phone',
                email='$email',
                iban='$iban',
                bank_name='$bank_name',
                hourly_rate='$hourly_rate',
                daily_hours='$daily_hours'
                WHERE id=$student_id";
        $message = "تم تحديث بيانات الطالب بنجاح!";
    } else {
        // Add new student
        $sql = "INSERT INTO student_workers (full_name, academic_id, department, major, phone, email, iban, bank_name, hourly_rate, daily_hours)
                VALUES ('$full_name', '$academic_id', '$department', '$major', '$phone', '$email', '$iban', '$bank_name', '$hourly_rate', '$daily_hours')";
        $message = "تم إضافة الطالب بنجاح!";
    }
    
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('✅ $message'); window.location='manage_students.php';</script>";
    } else {
        echo "<script>alert('❌ خطأ: " . $conn->error . "');</script>";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $sql = "DELETE FROM student_workers WHERE id=$id";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('✅ تم حذف الطالب بنجاح!'); window.location='manage_students.php';</script>";
    }
}

// Get student for editing
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM student_workers WHERE id=$id");
    if ($result->num_rows == 1) {
        $edit_student = $result->fetch_assoc();
    }
}

// Get all students
$students = $conn->query("SELECT * FROM student_workers ORDER BY created_at DESC");
$total_students = $students->num_rows;
?>

<div class="content">
    <h2 class="page-title">👥 إدارة طلاب التشغيل</h2>

    <div class="stats-box">
        ✅ إجمالي الطلاب المسجلين: <?= $total_students ?>
    </div>

    <!-- Add/Edit Form -->
    <div class="form-container" dir="rtl">
        <h3 style="text-align: center; margin-bottom: 20px;">
            <?= $edit_student ? '✏️ تعديل بيانات الطالب' : '➕ إضافة طالب جديد' ?>
        </h3>
        
        <form method="POST" action="">
            <?php if ($edit_student): ?>
                <input type="hidden" name="student_id" value="<?= $edit_student['id'] ?>">
            <?php endif; ?>
            
            <div class="form-group">
                <label for="full_name">👤 الاسم الكامل *</label>
                <input type="text" id="full_name" name="full_name" class="form-control" 
                       value="<?= $edit_student['full_name'] ?? '' ?>" 
                       placeholder="مثال: أحمد محمد علي" required>
            </div>

            <div class="form-group">
                <label for="academic_id">🔢 الرقم الأكاديمي *</label>
                <input type="text" id="academic_id" name="academic_id" class="form-control" 
                       value="<?= $edit_student['academic_id'] ?? '' ?>" 
                       placeholder="مثال: 202012345" required>
            </div>

            <div class="form-group">
                <label for="department">🏫 القسم</label>
                <input type="text" id="department" name="department" class="form-control" 
                       value="<?= $edit_student['department'] ?? '' ?>" 
                       placeholder="مثال: كلية ينبع الصناعية">
            </div>

            <div class="form-group">
                <label for="major">📚 التخصص</label>
                <input type="text" id="major" name="major" class="form-control" 
                       value="<?= $edit_student['major'] ?? '' ?>" 
                       placeholder="مثال: هندسة ميكانيكية">
            </div>

            <div class="form-group">
                <label for="phone">📱 رقم الجوال</label>
                <input type="text" id="phone" name="phone" class="form-control" 
                       value="<?= $edit_student['phone'] ?? '' ?>" 
                       placeholder="مثال: 0501234567">
            </div>

            <div class="form-group">
                <label for="email">📧 البريد الإلكتروني</label>
                <input type="email" id="email" name="email" class="form-control" 
                       value="<?= $edit_student['email'] ?? '' ?>" 
                       placeholder="مثال: student@rcjy.edu.sa">
            </div>

            <div class="form-group">
                <label for="iban">🏦 رقم الآيبان (IBAN)</label>
                <input type="text" id="iban" name="iban" class="form-control" 
                       value="<?= $edit_student['iban'] ?? '' ?>" 
                       placeholder="مثال: SA1234567890123456789012">
            </div>

            <div class="form-group">
                <label for="bank_name">🏛️ اسم البنك</label>
                <select id="bank_name" name="bank_name" class="form-control">
                    <?= bankOptions($edit_student['bank_name'] ?? '') ?>
                </select>
            </div>

            <div class="form-group">
                <label for="hourly_rate">💰 أجر الساعة (ريال)</label>
                <input type="number" step="0.01" id="hourly_rate" name="hourly_rate" class="form-control"
                       value="<?= $edit_student['hourly_rate'] ?? '20.00' ?>"
                       placeholder="20.00">
            </div>

            <div class="form-group">
                <label for="daily_hours">⏰ ساعات العمل اليومية</label>
                <select id="daily_hours" name="daily_hours" class="form-control">
                    <option value="2" <?= (isset($edit_student['daily_hours']) && $edit_student['daily_hours'] == 2) ? 'selected' : '' ?>>ساعتين (2)</option>
                    <option value="3" <?= (isset($edit_student['daily_hours']) && $edit_student['daily_hours'] == 3) ? 'selected' : '' ?>>ثلاث ساعات (3)</option>
                </select>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">
                    <?= $edit_student ? '💾 حفظ التعديلات' : '➕ إضافة الطالب' ?>
                </button>
                <?php if ($edit_student): ?>
                    <a href="manage_students.php" class="btn btn-secondary">❌ إلغاء التعديل</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Students List -->
    <div style="margin-top: 40px;">
        <h3 style="text-align: center; margin-bottom: 20px;" dir="rtl">📋 قائمة الطلاب المسجلين</h3>
        
        <table dir="rtl">
            <thead>
                <tr>
                    <th>م</th>
                    <th>الاسم الكامل</th>
                    <th>الرقم الأكاديمي</th>
                    <th>القسم</th>
                    <th>التخصص</th>
                    <th>الساعات</th>
                    <th>الجوال</th>
                    <th class="no-print">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($students->num_rows > 0): ?>
                    <?php $counter = 1; ?>
                    <?php while($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($student['full_name']) ?></td>
                            <td><?= htmlspecialchars($student['academic_id']) ?></td>
                            <td><?= htmlspecialchars($student['department']) ?></td>
                            <td><?= htmlspecialchars($student['major']) ?></td>
                            <td><?= ($student['daily_hours'] ?? 2) ?> ساعات</td>
                            <td><?= htmlspecialchars($student['phone']) ?></td>
                            <td class="no-print">
                                <a href="?edit=<?= $student['id'] ?>" class="btn btn-warning" style="padding: 6px 12px; margin: 2px;">
                                    ✏️ تعديل
                                </a>
                                <a href="?delete=<?= $student['id'] ?>" class="btn btn-danger" style="padding: 6px 12px; margin: 2px;"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا الطالب؟')">
                                    🗑️ حذف
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 30px;">
                            📚 لا يوجد طلاب مسجلين حالياً. ابدأ بإضافة طالب جديد!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
