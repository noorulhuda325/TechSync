<?php
/**
 * Admin - Manage Teachers
 * CRUD operations for teachers
 */

require_once '../includes/config.php';
requireRole('admin');

$page_title = 'Manage Teachers';

$conn = getDBConnection();
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_teacher'])) {
        $full_name = sanitizeInput($_POST['full_name']);
        $email = sanitizeInput($_POST['email']);
        $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $department_id = intval($_POST['department_id']);
        $phone = sanitizeInput($_POST['phone']);
        $address = sanitizeInput($_POST['address']);
        $hire_date = $_POST['hire_date'];
        
        // Generate Teacher ID
        $teacher_id = generateTeacherID();
        
        // Create user account
        $stmt = $conn->prepare("INSERT INTO users (email, password, role) VALUES (?, ?, 'teacher')");
        $stmt->bind_param("ss", $email, $password);
        
        if ($stmt->execute()) {
            $user_id = $stmt->insert_id;
            
            // Create teacher record
            $stmt2 = $conn->prepare("INSERT INTO teachers (user_id, teacher_id, full_name, department_id, phone, address, hire_date) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt2->bind_param("ississs", $user_id, $teacher_id, $full_name, $department_id, $phone, $address, $hire_date);
            
            if ($stmt2->execute()) {
                $success = "Teacher created successfully! Teacher ID: $teacher_id";
            } else {
                $error = "Error creating teacher: " . $stmt2->error;
                // Rollback user creation
                $conn->query("DELETE FROM users WHERE id = $user_id");
            }
            $stmt2->close();
        } else {
            $error = "Error creating user account: " . $stmt->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_teacher'])) {
        $teacher_id = intval($_POST['teacher_id']);
        $teacher = $conn->query("SELECT user_id FROM teachers WHERE id = $teacher_id")->fetch_assoc();
        
        if ($teacher) {
            $conn->query("DELETE FROM users WHERE id = " . $teacher['user_id']);
            $success = "Teacher deleted successfully!";
        }
    }
}

// Get all teachers with department info
$teachers = $conn->query("
    SELECT t.*, d.name as department_name, u.email 
    FROM teachers t 
    LEFT JOIN departments d ON t.department_id = d.id 
    LEFT JOIN users u ON t.user_id = u.id
    ORDER BY t.created_at DESC
")->fetch_all(MYSQLI_ASSOC);

// Get departments for dropdown
$departments = $conn->query("SELECT * FROM departments ORDER BY name")->fetch_all(MYSQLI_ASSOC);

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
        <h3 class="card-title">Teachers List</h3>
        <button class="btn btn-primary" onclick="openModal('addTeacherModal')">
            <i class="fas fa-plus"></i> Add New Teacher
        </button>
    </div>
    
    <div class="search-bar">
        <input type="text" id="searchTeachers" placeholder="Search teachers by name, ID, or email...">
    </div>
    
    <div class="table-container">
        <table class="table" id="teachersTable">
            <thead>
                <tr>
                    <th>Teacher ID</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Department</th>
                    <th>Phone</th>
                    <th>Hire Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($teachers as $teacher): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($teacher['teacher_id']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['full_name']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['email']); ?></td>
                        <td><?php echo htmlspecialchars($teacher['department_name'] ?? 'N/A'); ?></td>
                        <td><?php echo htmlspecialchars($teacher['phone'] ?? 'N/A'); ?></td>
                        <td><?php echo $teacher['hire_date'] ? formatDate($teacher['hire_date']) : 'N/A'; ?></td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="teacher_id" value="<?php echo $teacher['id']; ?>">
                                <button type="submit" name="delete_teacher" class="btn btn-sm btn-danger">
                                    <i class="fas fa-trash"></i> Delete
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Teacher Modal -->
<div class="modal" id="addTeacherModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Teacher</h3>
            <button class="modal-close" onclick="closeModal('addTeacherModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Full Name *</label>
                <input type="text" name="full_name" required>
            </div>
            <div class="form-group">
                <label>Email *</label>
                <input type="email" name="email" required>
            </div>
            <div class="form-group">
                <label>Password *</label>
                <input type="password" name="password" required minlength="6">
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Department *</label>
                    <select name="department_id" required>
                        <option value="">Select Department</option>
                        <?php foreach ($departments as $dept): ?>
                            <option value="<?php echo $dept['id']; ?>"><?php echo htmlspecialchars($dept['name']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Phone</label>
                    <input type="text" name="phone">
                </div>
            </div>
            <div class="form-group">
                <label>Address</label>
                <textarea name="address" rows="3"></textarea>
            </div>
            <div class="form-group">
                <label>Hire Date</label>
                <input type="date" name="hire_date" value="<?php echo date('Y-m-d'); ?>">
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addTeacherModal')">Cancel</button>
                <button type="submit" name="add_teacher" class="btn btn-primary">Create Teacher</button>
            </div>
        </form>
    </div>
</div>

<script>
initSearch('searchTeachers', 'teachersTable');
</script>

<?php include '../includes/footer.php'; ?>

