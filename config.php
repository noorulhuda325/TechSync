<?php
/**
 * TeachSync (EduTrack) - Configuration File
 * Database connection and system configuration
 */

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'teachsync');

// Application Configuration
define('APP_NAME', 'TeachSync');
define('APP_VERSION', '1.0.0');
define('BASE_URL', 'http://localhost/noor/');

// Timezone
date_default_timezone_set('UTC');

/**
 * Database Connection Function
 */
function getDBConnection() {
    static $conn = null;
    
    if ($conn === null) {
        try {
            $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
            
            if ($conn->connect_error) {
                die("Connection failed: " . $conn->connect_error);
            }
            
            $conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    return $conn;
}

/**
 * Check if user is logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

/**
 * Check if user has specific role
 */
function hasRole($role) {
    return isLoggedIn() && $_SESSION['role'] === $role;
}

/**
 * Require login - redirect to login if not logged in
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ' . BASE_URL . 'index.php');
        exit();
    }
}

/**
 * Require specific role - redirect to dashboard if wrong role
 */
function requireRole($role) {
    requireLogin();
    if (!hasRole($role)) {
        header('Location: ' . BASE_URL . getDashboardPath());
        exit();
    }
}

/**
 * Get dashboard path based on role
 */
function getDashboardPath() {
    if (!isLoggedIn()) {
        return 'index.php';
    }
    
    switch ($_SESSION['role']) {
        case 'admin':
            return 'admin/dashboard.php';
        case 'teacher':
            return 'teacher/dashboard.php';
        case 'student':
            return 'student/dashboard.php';
        default:
            return 'index.php';
    }
}

/**
 * Sanitize input data
 */
function sanitizeInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

/**
 * Generate random ID for teachers/students
 */
function generateTeacherID() {
    $conn = getDBConnection();
    do {
        $id = 'T-' . rand(1000, 9999);
        $result = $conn->query("SELECT id FROM teachers WHERE teacher_id = '$id'");
    } while ($result->num_rows > 0);
    return $id;
}

function generateStudentID() {
    $conn = getDBConnection();
    $year = date('Y');
    $counter = 1;
    do {
        $id = 'STU-' . $year . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);
        $result = $conn->query("SELECT id FROM students WHERE student_id = '$id'");
        if ($result->num_rows == 0) break;
        $counter++;
    } while (true);
    return $id;
}

/**
 * Format date for display
 */
function formatDate($date) {
    return date('M d, Y', strtotime($date));
}

/**
 * Format time for display
 */
function formatTime($time) {
    return date('h:i A', strtotime($time));
}

