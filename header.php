<?php
/**
 * Common Header File
 * Includes navigation and header elements
 */
if (!isLoggedIn()) {
    header('Location: ' . BASE_URL . 'index.php');
    exit();
}

$current_role = $_SESSION['role'];
$full_name = isset($_SESSION['full_name']) ? $_SESSION['full_name'] : 'User';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo isset($page_title) ? $page_title . ' - ' : ''; ?><?php echo APP_NAME; ?></title>
    <link rel="stylesheet" href="<?php echo BASE_URL; ?>assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="<?php echo $current_role; ?>-theme">
    <div class="app-wrapper">
        <!-- Sidebar Navigation -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <h2><?php echo APP_NAME; ?></h2>
                <button class="sidebar-toggle" id="sidebarToggle">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
            
            <nav class="sidebar-nav">
                <?php if ($current_role === 'admin'): ?>
                    <a href="<?php echo BASE_URL; ?>admin/dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> <span>Dashboard</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/teachers.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'teachers.php' ? 'active' : ''; ?>">
                        <i class="fas fa-chalkboard-teacher"></i> <span>Manage Teachers</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/students.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'students.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-graduate"></i> <span>Manage Students</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/classes.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'classes.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i> <span>Classes & Departments</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/timetable.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'timetable.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> <span>Timetable Builder</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/attendance.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-check"></i> <span>Attendance Analytics</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/rooms.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'rooms.php' ? 'active' : ''; ?>">
                        <i class="fas fa-door-open"></i> <span>Manage Rooms</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>admin/events.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check"></i> <span>Events</span>
                    </a>
                <?php elseif ($current_role === 'teacher'): ?>
                    <a href="<?php echo BASE_URL; ?>teacher/dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> <span>Dashboard</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>teacher/classes.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'classes.php' ? 'active' : ''; ?>">
                        <i class="fas fa-book"></i> <span>My Classes</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>teacher/students.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'students.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user-graduate"></i> <span>Students List</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>teacher/attendance.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-check"></i> <span>Mark Attendance</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>teacher/schedule.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'schedule.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> <span>Weekly Schedule</span>
                    </a>
                <?php elseif ($current_role === 'student'): ?>
                    <a href="<?php echo BASE_URL; ?>student/dashboard.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'dashboard.php' ? 'active' : ''; ?>">
                        <i class="fas fa-home"></i> <span>Dashboard</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>student/profile.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'profile.php' ? 'active' : ''; ?>">
                        <i class="fas fa-user"></i> <span>My Profile</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>student/attendance.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'attendance.php' ? 'active' : ''; ?>">
                        <i class="fas fa-clipboard-check"></i> <span>Attendance History</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>student/schedule.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'schedule.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-alt"></i> <span>Weekly Schedule</span>
                    </a>
                    <a href="<?php echo BASE_URL; ?>student/events.php" class="nav-item <?php echo basename($_SERVER['PHP_SELF']) === 'events.php' ? 'active' : ''; ?>">
                        <i class="fas fa-calendar-check"></i> <span>Calendar & Events</span>
                    </a>
                <?php endif; ?>
            </nav>
        </aside>
        
        <!-- Main Content Area -->
        <main class="main-content">
            <!-- Top Header Bar -->
            <header class="top-header">
                <div class="header-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?php echo isset($page_title) ? $page_title : 'Dashboard'; ?></h1>
                </div>
                <div class="header-right">
                    <button class="theme-toggle" id="themeToggle" title="Toggle Dark Mode">
                        <i class="fas fa-moon"></i>
                    </button>
                    <div class="user-menu">
                        <span class="user-name"><?php echo htmlspecialchars($full_name); ?></span>
                        <span class="user-role"><?php echo ucfirst($current_role); ?></span>
                    </div>
                    <a href="<?php echo BASE_URL; ?>logout.php" class="btn btn-logout">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </a>
                </div>
            </header>
            
            <!-- Page Content -->
            <div class="content-wrapper">

