<?php
/**
 * Teacher - Students List
 * View students in assigned classes (searchable)
 */

require_once '../includes/config.php';
requireRole('teacher');

$page_title = 'Students List';

$conn = getDBConnection();
$teacher_id = $_SESSION['teacher_id'] ?? null;
$selected_class_id = isset($_GET['class_id']) ? intval($_GET['class_id']) : 0;

// Get teacher's classes
$teacher_classes = $conn->query("
    SELECT c.* FROM classes c
    WHERE c.teacher_id = (SELECT id FROM teachers WHERE teacher_id = '$teacher_id')
    ORDER BY c.class_name
")->fetch_all(MYSQLI_ASSOC);

// Get students based on selected class or all classes
if ($selected_class_id > 0) {
    $students = $conn->query("
        SELECT s.*, c.class_code, c.class_name, cs.enrollment_date
        FROM students s
        INNER JOIN class_students cs ON s.id = cs.student_id
        INNER JOIN classes c ON cs.class_id = c.id
        WHERE cs.class_id = $selected_class_id
        AND c.teacher_id = (SELECT id FROM teachers WHERE teacher_id = '$teacher_id')
        ORDER BY s.full_name
    ")->fetch_all(MYSQLI_ASSOC);
} else {
    // Get all students from teacher's classes
    $class_ids = array_map(function($c) { return $c['id']; }, $teacher_classes);
    if (count($class_ids) > 0) {
        $class_ids_str = implode(',', $class_ids);
        $students = $conn->query("
            SELECT DISTINCT s.*, c.class_code, c.class_name, cs.enrollment_date
            FROM students s
            INNER JOIN class_students cs ON s.id = cs.student_id
            INNER JOIN classes c ON cs.class_id = c.id
            WHERE cs.class_id IN ($class_ids_str)
            ORDER BY s.full_name
        ")->fetch_all(MYSQLI_ASSOC);
    } else {
        $students = [];
    }
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Students List</h3>
    </div>
    
    <form method="GET" style="margin-bottom: 20px; padding: 20px; background: var(--bg-color); border-radius: 6px;">
        <div class="form-row">
            <div class="form-group">
                <label>Filter by Class</label>
                <select name="class_id" onchange="this.form.submit()">
                    <option value="0">All Classes</option>
                    <?php foreach ($teacher_classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>" <?php echo $selected_class_id == $class['id'] ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($class['class_code'] . ' - ' . $class['class_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
    </form>
    
    <div class="search-bar">
        <input type="text" id="searchStudents" placeholder="Search students by name, ID, or roll number...">
    </div>
    
    <div class="table-container">
        <table class="table" id="studentsTable">
            <thead>
                <tr>
                    <th>Student ID</th>
                    <th>Roll No</th>
                    <th>Full Name</th>
                    <th>Class</th>
                    <th>Section</th>
                    <th>Phone</th>
                    <th>Enrollment Date</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($students) > 0): ?>
                    <?php foreach ($students as $student): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                            <td><?php echo htmlspecialchars($student['roll_number']); ?></td>
                            <td><?php echo htmlspecialchars($student['full_name']); ?></td>
                            <td><?php echo htmlspecialchars($student['class_code'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['section'] ?? 'N/A'); ?></td>
                            <td><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></td>
                            <td><?php echo $student['enrollment_date'] ? formatDate($student['enrollment_date']) : 'N/A'; ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" class="text-center">No students found</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
initSearch('searchStudents', 'studentsTable');
</script>

<?php include '../includes/footer.php'; ?>

