<?php
/**
 * Teacher - Mark Attendance
 * Mark attendance for students (Present/Absent/Late)
 */

require_once '../includes/config.php';
requireRole('teacher');

$page_title = 'Mark Attendance';

$conn = getDBConnection();
$teacher_id = $_SESSION['teacher_id'] ?? null;
$teacher_db_id = $conn->query("SELECT id FROM teachers WHERE teacher_id = '$teacher_id'")->fetch_assoc()['id'] ?? null;

$error = '';
$success = '';

// Handle attendance submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['mark_attendance'])) {
    $class_id = intval($_POST['class_id']);
    $attendance_date = $_POST['attendance_date'];
    $hour_number = intval($_POST['hour_number']);
    $student_attendance = $_POST['student_attendance'] ?? [];
    
    // Verify teacher owns this class
    $verify = $conn->query("SELECT id FROM classes WHERE id = $class_id AND teacher_id = $teacher_db_id");
    if ($verify->num_rows > 0) {
        $marked = 0;
        foreach ($student_attendance as $student_id => $status) {
            $student_id = intval($student_id);
            $status = sanitizeInput($status);
            
            // Delete existing attendance for this date/hour
            $conn->query("DELETE FROM attendance WHERE student_id = $student_id AND class_id = $class_id AND attendance_date = '$attendance_date' AND hour_number = $hour_number");
            
            // Insert new attendance
            $stmt = $conn->prepare("INSERT INTO attendance (student_id, class_id, attendance_date, hour_number, status, marked_by) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("iisisi", $student_id, $class_id, $attendance_date, $hour_number, $status, $teacher_db_id);
            if ($stmt->execute()) {
                $marked++;
            }
            $stmt->close();
        }
        $success = "Attendance marked successfully for $marked students!";
    } else {
        $error = "You don't have permission to mark attendance for this class.";
    }
}

// Get selected parameters
$selected_class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;
$selected_date = isset($_GET['date']) ? $_GET['date'] : date('Y-m-d');
$selected_hour = isset($_GET['hour']) ? intval($_GET['hour']) : 1;

// Get teacher's classes
$teacher_classes = $conn->query("
    SELECT c.* FROM classes c
    WHERE c.teacher_id = (SELECT id FROM teachers WHERE teacher_id = '$teacher_id')
    ORDER BY c.class_name
")->fetch_all(MYSQLI_ASSOC);

// Get students for selected class
$students = [];
if ($selected_class_id > 0) {
    $students = $conn->query("
        SELECT s.*, 
               (SELECT status FROM attendance 
                WHERE student_id = s.id 
                AND class_id = $selected_class_id 
                AND attendance_date = '$selected_date' 
                AND hour_number = $selected_hour 
                LIMIT 1) as current_status
        FROM students s
        INNER JOIN class_students cs ON s.id = cs.student_id
        WHERE cs.class_id = $selected_class_id
        ORDER BY s.roll_number, s.full_name
    ")->fetch_all(MYSQLI_ASSOC);
}

$hours = [1, 2, 3, 4, 5, 6];

include '../includes/header.php';
?>

<?php if ($error): ?>
    <div class="alert alert-error"><?php echo $error; ?></div>
<?php endif; ?>

<?php if ($success): ?>
    <div class="alert alert-success"><?php echo $success; ?></div>
<?php endif; ?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Mark Attendance</h3>
    </div>
    
    <form method="GET" style="margin-bottom: 20px; padding: 20px; background: var(--bg-color); border-radius: 6px;">
        <div class="form-row">
            <div class="form-group">
                <label>Select Class *</label>
                <select name="class_id" required onchange="this.form.submit()">
                    <option value="">Select Class</option>
                    <?php foreach ($teacher_classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $selected_class_id == $class['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_code'] . ' - ' . $class['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date *</label>
                <input type="date" name="date" value="<?php echo $selected_date; ?>" required onchange="this.form.submit()">
            </div>
            <div class="form-group">
                <label>Hour *</label>
                <select name="hour" required onchange="this.form.submit()">
                    <?php foreach ($hours as $hour): ?>
                        <option value="<?php echo $hour; ?>" <?php echo $selected_hour == $hour ? 'selected' : ''; ?>>
                            Hour <?php echo $hour; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
    
    <?php if ($selected_class_id > 0 && count($students) > 0): ?>
        <form method="POST">
            <input type="hidden" name="class_id" value="<?php echo $selected_class_id; ?>">
            <input type="hidden" name="attendance_date" value="<?php echo $selected_date; ?>">
            <input type="hidden" name="hour_number" value="<?php echo $selected_hour; ?>">
            
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Roll No</th>
                            <th>Student Name</th>
                            <th>Student ID</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($students as $student): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                                <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                <td>
                                    <div style="display: flex; gap: 5px;">
                                        <button type="button" 
                                                class="btn btn-sm <?php echo ($student['current_status'] ?? '') === 'P' ? 'btn-success active' : 'btn-success'; ?>" 
                                                onclick="toggleAttendanceStatus(this, 'P', <?php echo $student['id']; ?>)"
                                                style="<?php echo ($student['current_status'] ?? '') === 'P' ? 'opacity: 1;' : 'opacity: 0.6;'; ?>">
                                            <i class="fas fa-check"></i> Present
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm <?php echo ($student['current_status'] ?? '') === 'A' ? 'btn-danger active' : 'btn-danger'; ?>" 
                                                onclick="toggleAttendanceStatus(this, 'A', <?php echo $student['id']; ?>)"
                                                style="<?php echo ($student['current_status'] ?? '') === 'A' ? 'opacity: 1;' : 'opacity: 0.6;'; ?>">
                                            <i class="fas fa-times"></i> Absent
                                        </button>
                                        <button type="button" 
                                                class="btn btn-sm <?php echo ($student['current_status'] ?? '') === 'L' ? 'btn-warning active' : 'btn-warning'; ?>" 
                                                onclick="toggleAttendanceStatus(this, 'L', <?php echo $student['id']; ?>)"
                                                style="<?php echo ($student['current_status'] ?? '') === 'L' ? 'opacity: 1;' : 'opacity: 0.6;'; ?>">
                                            <i class="fas fa-clock"></i> Late
                                        </button>
                                    </div>
                                    <input type="hidden" name="student_attendance[<?php echo $student['id']; ?>]" 
                                           id="status_<?php echo $student['id']; ?>" 
                                           value="<?php echo htmlspecialchars($student['current_status'] ?? 'A'); ?>">
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div style="margin-top: 20px; text-align: right;">
                <button type="submit" name="mark_attendance" class="btn btn-primary btn-lg">
                    <i class="fas fa-save"></i> Save Attendance
                </button>
            </div>
        </form>
    <?php elseif ($selected_class_id > 0): ?>
        <div style="padding: 40px; text-align: center; color: var(--text-light);">
            <p>No students enrolled in this class.</p>
        </div>
    <?php else: ?>
        <div style="padding: 40px; text-align: center; color: var(--text-light);">
            <p>Please select a class to mark attendance.</p>
        </div>
    <?php endif; ?>
</div>

<script>
function toggleAttendanceStatus(button, status, studentId) {
    // Remove active class from all buttons in the same row
    const row = button.closest('tr');
    const buttons = row.querySelectorAll('button');
    buttons.forEach(btn => {
        btn.classList.remove('active');
        btn.style.opacity = '0.6';
    });
    
    // Add active class to clicked button
    button.classList.add('active');
    button.style.opacity = '1';
    
    // Update hidden input
    document.getElementById('status_' + studentId).value = status;
}
</script>

<?php include '../includes/footer.php'; ?>

