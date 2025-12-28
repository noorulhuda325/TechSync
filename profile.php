<?php
/**
 * Student - My Profile
 * View and update profile details
 */

require_once '../includes/config.php';
requireRole('student');

$page_title = 'My Profile';

$conn = getDBConnection();
$student_id = $_SESSION['student_id'] ?? null;

// Get student info with class details
$student_query = $conn->query("
    SELECT s.*, c.class_code, c.class_name, c.id as class_db_id, d.name as department_name
    FROM students s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN departments d ON c.department_id = d.id
    WHERE s.student_id = '$student_id'
");
if (!$student_query) {
    die("Database error: " . $conn->error);
}
$student = $student_query->fetch_assoc();
if (!$student) {
    die("Student not found. Please contact administrator.");
}

// Get user email
$email_query = $conn->query("SELECT email FROM users WHERE id = " . $student['user_id']);
$user_email = ($email_query && $email_query->num_rows > 0) ? $email_query->fetch_assoc()['email'] : 'N/A';

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Profile</h3>
    </div>
    
    <div style="padding: 30px;">
        <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 30px;">
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--border-color);">
                    Personal Information
                </h4>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Student ID</label>
                    <div style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($student['student_id']); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Roll Number</label>
                    <div style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($student['roll_number']); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Full Name</label>
                    <div style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($student['full_name']); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Email</label>
                    <div style="font-size: 1rem;"><?php echo htmlspecialchars($user_email); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Phone</label>
                    <div style="font-size: 1rem;"><?php echo htmlspecialchars($student['phone'] ?? 'N/A'); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Address</label>
                    <div style="font-size: 1rem;"><?php echo htmlspecialchars($student['address'] ?? 'N/A'); ?></div>
                </div>
            </div>
            
            <div>
                <h4 style="color: var(--primary-color); margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid var(--border-color);">
                    Academic Information
                </h4>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Class</label>
                    <div style="font-size: 1.1rem; font-weight: 600;"><?php echo htmlspecialchars($student['class_code'] ?? 'N/A'); ?></div>
                    <div style="font-size: 0.9rem; color: var(--text-light);"><?php echo htmlspecialchars($student['class_name'] ?? ''); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Department</label>
                    <div style="font-size: 1rem;"><?php echo htmlspecialchars($student['department_name'] ?? 'N/A'); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Section</label>
                    <div style="font-size: 1rem;"><?php echo htmlspecialchars($student['section'] ?? 'N/A'); ?></div>
                </div>
                <div style="margin-bottom: 15px;">
                    <label style="display: block; color: var(--text-light); font-size: 0.9rem; margin-bottom: 5px;">Enrollment Date</label>
                    <div style="font-size: 1rem;"><?php echo $student['enrollment_date'] ? formatDate($student['enrollment_date']) : 'N/A'; ?></div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

