<?php
include 'db_connect.php';
include 'header.php';

// Get all coop students
$students = $conn->query("SELECT * FROM coop_students ORDER BY full_name ASC");
?>

<div class="content">
    <h2 class="page-title">🎓 نموذج صرف المكافأة — طلاب التدريب التعاوني</h2>

    <div class="form-container" dir="rtl">
        <h3 style="text-align: center; margin-bottom: 20px;">📝 اختر الطلاب وحدد فترة المكافأة</h3>

        <form id="rewardForm" onsubmit="return openRewardPrint()">
            <!-- Period -->
            <div style="display: flex; gap: 20px; flex-wrap: wrap; margin-bottom: 20px;">
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label for="period_from">📅 فترة المكافأة — من (يوم/شهر)</label>
                    <input type="text" id="period_from" name="period_from" class="form-control"
                           placeholder="مثال: 1/11" required>
                </div>
                <div class="form-group" style="flex: 1; min-width: 200px;">
                    <label for="period_to">📅 فترة المكافأة — إلى (يوم/شهر)</label>
                    <input type="text" id="period_to" name="period_to" class="form-control"
                           placeholder="مثال: 30/11" required>
                </div>
            </div>

            <!-- Students Selection -->
            <div style="margin-bottom: 15px;">
                <label style="font-weight: bold; font-size: 15px;">👥 اختر الطلاب:</label>
                <div style="margin-top: 8px; margin-bottom: 8px;">
                    <label style="cursor: pointer;">
                        <input type="checkbox" id="selectAll" onclick="toggleAll(this)"> تحديد الكل
                    </label>
                </div>
            </div>

            <table dir="rtl" style="margin-bottom: 20px;">
                <thead>
                    <tr>
                        <th style="width: 40px;">✔</th>
                        <th>م</th>
                        <th>الاسم الكامل</th>
                        <th>الرقم الأكاديمي</th>
                        <th>التخصص</th>
                        <th>البنك</th>
                        <th>عدد أيام الغياب</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($students && $students->num_rows > 0): ?>
                        <?php $counter = 1; ?>
                        <?php while($s = $students->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="student-check" name="students[]"
                                           value="<?= $s['id'] ?>"
                                           data-name="<?= htmlspecialchars($s['full_name']) ?>"
                                           data-iban="<?= htmlspecialchars($s['iban'] ?? '') ?>"
                                           data-bank="<?= htmlspecialchars($s['bank_name'] ?? '') ?>">
                                </td>
                                <td><?= $counter++ ?></td>
                                <td><?= htmlspecialchars($s['full_name']) ?></td>
                                <td><?= htmlspecialchars($s['academic_id']) ?></td>
                                <td><?= htmlspecialchars($s['major'] ?? '—') ?></td>
                                <td><?= htmlspecialchars($s['bank_name'] ?? '—') ?></td>
                                <td>
                                    <input type="text" name="absence_<?= $s['id'] ?>" class="form-control"
                                           style="width: 100px; text-align: center; padding: 4px;"
                                           value="لا يوجد" placeholder="0">
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 30px;">
                                🎓 لا يوجد طلاب مسجلين. <a href="manage_coop_students.php">أضف طلاب أولاً</a>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>

            <div class="action-buttons">
                <button type="submit" class="btn btn-primary" style="font-size: 16px; padding: 12px 40px;">
                    🖨️ عرض نموذج الصرف للطباعة
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleAll(el) {
    document.querySelectorAll('.student-check').forEach(cb => cb.checked = el.checked);
}

function openRewardPrint() {
    var checked = document.querySelectorAll('.student-check:checked');
    if (checked.length === 0) {
        alert('❌ اختر طالب واحد على الأقل');
        return false;
    }

    var periodFrom = document.getElementById('period_from').value;
    var periodTo   = document.getElementById('period_to').value;
    if (!periodFrom || !periodTo) {
        alert('❌ حدد فترة المكافأة');
        return false;
    }

    // Build data array
    var studentsData = [];
    checked.forEach(function(cb) {
        var sid = cb.value;
        var absenceInput = document.querySelector('input[name="absence_' + sid + '"]');
        studentsData.push({
            id: sid,
            name: cb.dataset.name,
            iban: cb.dataset.iban,
            bank: cb.dataset.bank,
            absence: absenceInput ? absenceInput.value : 'لا يوجد'
        });
    });

    // Store in sessionStorage and open print page
    sessionStorage.setItem('reward_students', JSON.stringify(studentsData));
    sessionStorage.setItem('reward_period_from', periodFrom);
    sessionStorage.setItem('reward_period_to', periodTo);

    window.open('coop_reward_print.php', '_blank');
    return false;
}
</script>

<?php include 'footer.php'; ?>
