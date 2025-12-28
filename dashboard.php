<?php
/**
 * Teacher Dashboard
 * Overview of classes, students, and today's attendance
 */

require_once '../includes/config.php';
requireRole('teacher');

$page_title = 'Teacher Dashboard';

$conn = getDBConnection();
$teacher_id = $_SESSION['teacher_id'] ?? null;

// Get teacher's classes
$teacher_classes = $conn->query("
    SELECT c.*, d.name as department_name, r.room_number,
           (SELECT COUNT(*) FROM class_students WHERE class_id = c.id) as student_count
    FROM classes c
    LEFT JOIN departments d ON c.department_id = d.id
    LEFT JOIN rooms r ON c.room_id = r.id
    WHERE c.teacher_id = (SELECT id FROM teachers WHERE teacher_id = '$teacher_id')
")->fetch_all(MYSQLI_ASSOC);

$total_classes = count($teacher_classes);
$total_students = 0;
foreach ($teacher_classes as $class) {
    $total_students += $class['student_count'];
}

// Today's attendance summary
$today = date('Y-m-d');
$today_stats = $conn->query("
    SELECT 
        COUNT(*) as total,
        SUM(CASE WHEN status = 'P' THEN 1 ELSE 0 END) as present,
        SUM(CASE WHEN status = 'A' THEN 1 ELSE 0 END) as absent,
        SUM(CASE WHEN status = 'L' THEN 1 ELSE 0 END) as late
    FROM attendance a
    WHERE a.marked_by = (SELECT id FROM teachers WHERE teacher_id = '$teacher_id')
    AND a.attendance_date = '$today'
")->fetch_assoc();

// Upcoming events
$upcoming_events = $conn->query("
    SELECT * FROM events 
    WHERE event_date >= CURDATE() 
    ORDER BY event_date ASC 
    LIMIT 5
")->fetch_all(MYSQLI_ASSOC);

include '../includes/header.php';
?>

<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-title">Total Classes</span>
            <i class="fas fa-book stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?php echo $total_classes; ?></div>
        <div class="stat-card-change">
            <i class="fas fa-chalkboard"></i> Assigned
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-title">Total Students</span>
            <i class="fas fa-user-graduate stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?php echo $total_students; ?></div>
        <div class="stat-card-change">
            <i class="fas fa-users"></i> Enrolled
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-title">Today's Attendance</span>
            <i class="fas fa-clipboard-check stat-card-icon"></i>
        </div>
        <div class="stat-card-value"><?php echo $today_stats['total'] ?? 0; ?></div>
        <div class="stat-card-change">
            <i class="fas fa-calendar-day"></i> Records
        </div>
    </div>
    
    <div class="stat-card">
        <div class="stat-card-header">
            <span class="stat-card-title">Present Today</span>
            <i class="fas fa-check-circle stat-card-icon" style="color: var(--success-color);"></i>
        </div>
        <div class="stat-card-value" style="color: var(--success-color);"><?php echo $today_stats['present'] ?? 0; ?></div>
        <div class="stat-card-change">
            <span style="color: var(--success-color);">
                <?php echo $today_stats['total'] > 0 ? round(($today_stats['present'] / $today_stats['total']) * 100, 1) : 0; ?>%
            </span>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 20px;">
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">My Classes</h3>
            <a href="classes.php" class="btn btn-sm btn-primary">View All</a>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Class Code</th>
                        <th>Class Name</th>
                        <th>Students</th>
                        <th>Room</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($teacher_classes) > 0): ?>
                        <?php foreach (array_slice($teacher_classes, 0, 5) as $class): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($class['class_code']); ?></td>
                                <td><?php echo htmlspecialchars($class['class_name']); ?></td>
                                <td><?php echo $class['student_count']; ?></td>
                                <td><?php echo htmlspecialchars($class['room_number'] ?? 'N/A'); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" class="text-center">No classes assigned</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    
    <div class="card">
        <div class="card-header">
            <h3 class="card-title">Upcoming Events</h3>
        </div>
        <div style="padding: 10px;">
            <?php if (count($upcoming_events) > 0): ?>
                <?php foreach ($upcoming_events as $event): ?>
                    <div style="padding: 10px; margin-bottom: 10px; border-left: 3px solid var(--primary-color); background: var(--bg-color); border-radius: 4px;">
                        <strong><?php echo htmlspecialchars($event['title']); ?></strong><br>
                        <small style="color: var(--text-light);">
                            <?php echo formatDate($event['event_date']); ?>
                            <?php if ($event['event_time']): ?>
                                at <?php echo formatTime($event['event_time']); ?>
                            <?php endif; ?>
                        </small><br>
                        <span class="badge badge-<?php echo $event['event_type'] === 'exam' ? 'danger' : ($event['event_type'] === 'meeting' ? 'info' : 'warning'); ?>">
                            <?php echo ucfirst($event['event_type']); ?>
                        </span>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p class="text-center" style="color: var(--text-light);">No upcoming events</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Today's Attendance Summary</h3>
    </div>
    <div class="stats-grid" style="grid-template-columns: repeat(4, 1fr);">
        <div>
            <div style="font-size: 0.9rem; color: var(--text-light); margin-bottom: 5px;">Total Records</div>
            <div style="font-size: 1.8rem; font-weight: 700;"><?php echo $today_stats['total'] ?? 0; ?></div>
        </div>
        <div>
            <div style="font-size: 0.9rem; color: var(--success-color); margin-bottom: 5px;">Present</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: var(--success-color);"><?php echo $today_stats['present'] ?? 0; ?></div>
        </div>
        <div>
            <div style="font-size: 0.9rem; color: var(--danger-color); margin-bottom: 5px;">Absent</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: var(--danger-color);"><?php echo $today_stats['absent'] ?? 0; ?></div>
        </div>
        <div>
            <div style="font-size: 0.9rem; color: var(--warning-color); margin-bottom: 5px;">Late</div>
            <div style="font-size: 1.8rem; font-weight: 700; color: var(--warning-color);"><?php echo $today_stats['late'] ?? 0; ?></div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

