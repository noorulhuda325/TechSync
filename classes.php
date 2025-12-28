<?php
/**
 * Teacher - My Classes
 * View assigned classes and student lists
 */

require_once '../includes/config.php';
requireRole('teacher');

$page_title = 'My Classes';

$conn = getDBConnection();
$teacher_id = $_SESSION['teacher_id'] ?? null;

// Get teacher's classes with student counts
$classes = $conn->query("
    SELECT c.*, d.name as department_name, r.room_number,
           (SELECT COUNT(*) FROM class_students WHERE class_id = c.id) as student_count
    FROM classes c
    LEFT JOIN departments d ON c.department_id = d.id
    LEFT JOIN rooms r ON c.room_id = r.id
    WHERE c.teacher_id = (SELECT id FROM teachers WHERE teacher_id = '$teacher_id')
    ORDER BY c.class_name
")->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Assigned Classes</h3>
    </div>
    
    <?php if (count($classes) > 0): ?>
        <div style="display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; padding: 20px;">
            <?php foreach ($classes as $class): ?>
                <div class="card" style="padding: 20px;">
                    <h4 style="color: var(--primary-color); margin-bottom: 10px;">
                        <?php echo htmlspecialchars($class['class_code']); ?>
                    </h4>
                    <p style="font-weight: 600; margin-bottom: 15px;">
                        <?php echo htmlspecialchars($class['class_name']); ?>
                    </p>
                    <div style="margin-bottom: 10px;">
                        <small style="color: var(--text-light);">Department:</small><br>
                        <?php echo htmlspecialchars($class['department_name'] ?? 'N/A'); ?>
                    </div>
                    <div style="margin-bottom: 10px;">
                        <small style="color: var(--text-light);">Room:</small><br>
                        <?php echo htmlspecialchars($class['room_number'] ?? 'N/A'); ?>
                    </div>
                    <div style="margin-bottom: 15px;">
                        <small style="color: var(--text-light);">Students:</small><br>
                        <strong style="font-size: 1.2rem;"><?php echo $class['student_count']; ?></strong> enrolled
                    </div>
                    <div style="display: flex; gap: 10px;">
                        <a href="students.php?class_id=<?php echo $class['id']; ?>" class="btn btn-primary btn-sm">
                            <i class="fas fa-users"></i> View Students
                        </a>
                        <a href="attendance.php?class_id=<?php echo $class['id']; ?>" class="btn btn-success btn-sm">
                            <i class="fas fa-clipboard-check"></i> Mark Attendance
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="padding: 40px; text-align: center; color: var(--text-light);">
            <i class="fas fa-book" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
            <p>No classes assigned yet.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

