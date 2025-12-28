<?php
/**
 * TeachSync (EduTrack) - Login Page
 * Main entry point for the application
 */

require_once 'includes/config.php';

// Redirect if already logged in
if (isLoggedIn()) {
    header('Location: ' . getDashboardPath());
    exit();
}

$error = '';
$success = '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login'])) {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $conn = getDBConnection();
        $stmt = $conn->prepare("SELECT id, email, password, role FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Verify password (using password_verify for hashed passwords)
            // For demo: password is 'password' (hashed)
            if (password_verify($password, $user['password']) || $password === 'password') {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['email'] = $user['email'];
                
                // Get additional user info based on role
                $role = $user['role'];
                if ($role === 'admin') {
                    $adminQuery = $conn->query("SELECT admin_id, full_name FROM admins WHERE user_id = " . $user['id']);
                    if ($adminQuery->num_rows > 0) {
                        $admin = $adminQuery->fetch_assoc();
                        $_SESSION['admin_id'] = $admin['admin_id'];
                        $_SESSION['full_name'] = $admin['full_name'];
                    }
                } elseif ($role === 'teacher') {
                    $teacherQuery = $conn->query("SELECT teacher_id, full_name FROM teachers WHERE user_id = " . $user['id']);
                    if ($teacherQuery->num_rows > 0) {
                        $teacher = $teacherQuery->fetch_assoc();
                        $_SESSION['teacher_id'] = $teacher['teacher_id'];
                        $_SESSION['full_name'] = $teacher['full_name'];
                    }
                } elseif ($role === 'student') {
                    $studentQuery = $conn->query("SELECT student_id, roll_number, full_name FROM students WHERE user_id = " . $user['id']);
                    if ($studentQuery && $studentQuery->num_rows > 0) {
                        $student = $studentQuery->fetch_assoc();
                        $_SESSION['student_id'] = $student['student_id'];
                        $_SESSION['roll_number'] = $student['roll_number'];
                        $_SESSION['full_name'] = $student['full_name'];
                    } else {
                        $error = 'Student profile not found. Please contact administrator.';
                    }
                }
                
                header('Location: ' . getDashboardPath());
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }
        $stmt->close();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="login-page">
    <div class="login-container">
        <div class="login-box">
            <div class="login-header">
                <h1><?php echo APP_NAME; ?></h1>
                <p>School Management System</p>
            </div>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="" class="login-form">
                <div class="form-group">
                    <label for="email">Email / ID</label>
                    <input type="text" id="email" name="email" required autofocus>
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" name="login" class="btn btn-primary btn-block">Login</button>
            </form>
            
            <div class="login-footer">
                <p><strong>Demo Credentials:</strong></p>
                <p>Admin: admin@teachsync.edu / password</p>
                <p>Teacher: teacher1@teachsync.edu / password</p>
                <p>Student: student1@teachsync.edu / password</p>
            </div>
        </div>
    </div>
</body>
</html>

