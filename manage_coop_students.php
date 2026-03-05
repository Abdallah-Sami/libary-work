<?php
include 'db_connect.php';
require_once 'helpers.php';

// Auto-create table if not exists
$conn->query("CREATE TABLE IF NOT EXISTS coop_students (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    academic_id VARCHAR(100) NOT NULL,
    department VARCHAR(255),
    major VARCHAR(255),
    phone VARCHAR(20),
    email VARCHAR(255),
    iban VARCHAR(100),
    bank_name VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

include 'header.php';

// Handle Add/Edit Student
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name  = $conn->real_escape_string($_POST['full_name']);
    $academic_id = $conn->real_escape_string($_POST['academic_id']);
    $department = $conn->real_escape_string($_POST['department']);
    $major      = $conn->real_escape_string($_POST['major']);
    $phone      = $conn->real_escape_string($_POST['phone']);
    $email      = $conn->real_escape_string($_POST['email']);
    $iban       = $conn->real_escape_string($_POST['iban']);
    $bank_name  = $conn->real_escape_string($_POST['bank_name']);

    if (isset($_POST['student_id']) && !empty($_POST['student_id'])) {
        $student_id = intval($_POST['student_id']);
        $sql = "UPDATE coop_students SET
                full_name='$full_name',
                academic_id='$academic_id',
                department='$department',
                major='$major',
                phone='$phone',
                email='$email',
                iban='$iban',
                bank_name='$bank_name'
                WHERE id=$student_id";
        $message = "تم تحديث بيانات الطالب بنجاح!";
    } else {
        $sql = "INSERT INTO coop_students (full_name, academic_id, department, major, phone, email, iban, bank_name)
                VALUES ('$full_name', '$academic_id', '$department', '$major', '$phone', '$email', '$iban', '$bank_name')";
        $message = "تم إضافة الطالب بنجاح!";
    }

    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('✅ $message'); window.location='manage_coop_students.php';</script>";
    } else {
        echo "<script>alert('❌ خطأ: " . $conn->error . "');</script>";
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);
    $conn->query("DELETE FROM coop_students WHERE id=$id");
    echo "<script>alert('✅ تم حذف الطالب بنجاح!'); window.location='manage_coop_students.php';</script>";
}

// Get student for editing
$edit_student = null;
if (isset($_GET['edit'])) {
    $id = intval($_GET['edit']);
    $result = $conn->query("SELECT * FROM coop_students WHERE id=$id");
    if ($result->num_rows == 1) {
        $edit_student = $result->fetch_assoc();
    }
}

// Get all students
$students = $conn->query("SELECT * FROM coop_students ORDER BY created_at DESC");
$total_students = $students->num_rows;
?>

<div class="content">
    <h2 class="page-title">🎓 إدارة طلاب التدريب التعاوني</h2>

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
                       value="<?= htmlspecialchars($edit_student['full_name'] ?? '') ?>"
                       placeholder="مثال: أحمد محمد علي" required>
            </div>

            <div class="form-group">
                <label for="academic_id">🔢 الرقم الأكاديمي *</label>
                <input type="text" id="academic_id" name="academic_id" class="form-control"
                       value="<?= htmlspecialchars($edit_student['academic_id'] ?? '') ?>"
                       placeholder="مثال: 202012345" required>
            </div>

            <div class="form-group">
                <label for="department">🏫 القسم</label>
                <input type="text" id="department" name="department" class="form-control"
                       value="<?= htmlspecialchars($edit_student['department'] ?? '') ?>"
                       placeholder="مثال: كلية ينبع الصناعية">
            </div>

            <div class="form-group">
                <label for="major">📚 التخصص</label>
                <input type="text" id="major" name="major" class="form-control"
                       value="<?= htmlspecialchars($edit_student['major'] ?? '') ?>"
                       placeholder="مثال: هندسة ميكانيكية">
            </div>

            <div class="form-group">
                <label for="phone">📱 رقم الجوال</label>
                <input type="text" id="phone" name="phone" class="form-control"
                       value="<?= htmlspecialchars($edit_student['phone'] ?? '') ?>"
                       placeholder="مثال: 0501234567">
            </div>

            <div class="form-group">
                <label for="email">📧 البريد الإلكتروني</label>
                <input type="email" id="email" name="email" class="form-control"
                       value="<?= htmlspecialchars($edit_student['email'] ?? '') ?>"
                       placeholder="مثال: student@rcjy.edu.sa">
            </div>

            <div class="form-group">
                <label for="iban">🏦 رقم الآيبان (IBAN)</label>
                <input type="text" id="iban" name="iban" class="form-control"
                       value="<?= htmlspecialchars($edit_student['iban'] ?? '') ?>"
                       placeholder="مثال: SA1234567890123456789012">
            </div>

            <div class="form-group">
                <label for="bank_name">🏛️ اسم البنك</label>
                <select id="bank_name" name="bank_name" class="form-control">
                    <?= bankOptions($edit_student['bank_name'] ?? '') ?>
                </select>
            </div>

            <div class="action-buttons">
                <button type="submit" class="btn btn-primary">
                    <?= $edit_student ? '💾 حفظ التعديلات' : '➕ إضافة الطالب' ?>
                </button>
                <?php if ($edit_student): ?>
                    <a href="manage_coop_students.php" class="btn btn-secondary">❌ إلغاء التعديل</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Students List -->
    <div style="margin-top: 40px;">
        <h3 style="text-align: center; margin-bottom: 20px;" dir="rtl">📋 قائمة طلاب التدريب التعاوني</h3>

        <table dir="rtl">
            <thead>
                <tr>
                    <th>م</th>
                    <th>الاسم الكامل</th>
                    <th>الرقم الأكاديمي</th>
                    <th>القسم</th>
                    <th>التخصص</th>
                    <th>الجوال</th>
                    <th class="no-print">الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($total_students > 0): ?>
                    <?php $counter = 1; ?>
                    <?php while($student = $students->fetch_assoc()): ?>
                        <tr>
                            <td><?= $counter++ ?></td>
                            <td><?= htmlspecialchars($student['full_name']) ?></td>
                            <td><?= htmlspecialchars($student['academic_id']) ?></td>
                            <td><?= htmlspecialchars($student['department']) ?></td>
                            <td><?= htmlspecialchars($student['major']) ?></td>
                            <td><?= htmlspecialchars($student['phone']) ?></td>
                            <td class="no-print">
                                <a href="?edit=<?= $student['id'] ?>" class="btn btn-warning" style="padding:6px 12px; margin:2px;">
                                    ✏️ تعديل
                                </a>
                                <a href="?delete=<?= $student['id'] ?>" class="btn btn-danger" style="padding:6px 12px; margin:2px;"
                                   onclick="return confirm('هل أنت متأكد من حذف هذا الطالب؟')">
                                    🗑️ حذف
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 30px;">
                            🎓 لا يوجد طلاب مسجلين حالياً. ابدأ بإضافة طالب جديد!
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php include 'footer.php'; ?>
