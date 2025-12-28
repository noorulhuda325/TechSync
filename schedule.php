<?php
/**
 * Teacher - Weekly Schedule
 * View color-coded weekly schedule
 */

require_once '../includes/config.php';
requireRole('teacher');

$page_title = 'Weekly Schedule';

$conn = getDBConnection();
$teacher_id = $_SESSION['teacher_id'] ?? null;

// Get teacher's schedule
$schedules = $conn->query("
    SELECT s.*, c.class_code, c.class_name, r.room_number
    FROM schedules s
    INNER JOIN classes c ON s.class_id = c.id
    LEFT JOIN rooms r ON s.room_id = r.id
    WHERE c.teacher_id = (SELECT id FROM teachers WHERE teacher_id = '$teacher_id')
    ORDER BY 
        FIELD(s.day_of_week, 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'),
        s.hour_number
")->fetch_all(MYSQLI_ASSOC);

// Days of week
$days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
$hours = [1, 2, 3, 4, 5, 6];

// Build timetable grid
$timetable = [];
foreach ($schedules as $schedule) {
    $day = $schedule['day_of_week'];
    $hour = $schedule['hour_number'];
    if (!isset($timetable[$day])) {
        $timetable[$day] = [];
    }
    $timetable[$day][$hour] = $schedule;
}

// Color mapping for classes
$class_colors = [];
$color_palette = ['#800020', '#708090', '#A0522D', '#556B2F', '#8B4513', '#2F4F4F'];
$color_index = 0;
foreach ($schedules as $schedule) {
    if (!isset($class_colors[$schedule['class_id']])) {
        $class_colors[$schedule['class_id']] = $color_palette[$color_index % count($color_palette)];
        $color_index++;
    }
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">My Weekly Schedule</h3>
    </div>
    
    <div class="table-container">
        <table class="timetable">
            <thead>
                <tr>
                    <th>Time / Day</th>
                    <?php foreach ($days as $day): ?>
                        <th><?php echo $day; ?></th>
                    <?php endforeach; ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($hours as $hour): ?>
                    <tr>
                        <th>Hour <?php echo $hour; ?></th>
                        <?php foreach ($days as $day): ?>
                            <td>
                                <?php if (isset($timetable[$day][$hour])): ?>
                                    <?php 
                                    $schedule = $timetable[$day][$hour];
                                    $bg_color = $class_colors[$schedule['class_id']] ?? '#800020';
                                    ?>
                                    <div class="timetable-cell" style="background: <?php echo $bg_color; ?>20; border-left-color: <?php echo $bg_color; ?>;">
                                        <strong style="color: <?php echo $bg_color; ?>;"><?php echo htmlspecialchars($schedule['class_code']); ?></strong><br>
                                        <?php echo htmlspecialchars($schedule['class_name']); ?><br>
                                        <small>Room: <?php echo htmlspecialchars($schedule['room_number'] ?? 'N/A'); ?></small><br>
                                        <?php if ($schedule['start_time']): ?>
                                            <small><?php echo formatTime($schedule['start_time']); ?> - <?php echo formatTime($schedule['end_time']); ?></small>
                                        <?php endif; ?>
                                    </div>
                                <?php else: ?>
                                    <div style="color: var(--text-light); font-size: 0.85rem;">Free</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<div class="card" style="margin-top: 20px;">
    <div class="card-header">
        <h3 class="card-title">Class Legend</h3>
    </div>
    <div style="display: flex; flex-wrap: wrap; gap: 15px; padding: 20px;">
        <?php 
        $displayed_classes = [];
        foreach ($schedules as $schedule): 
            if (!in_array($schedule['class_id'], $displayed_classes)):
                $displayed_classes[] = $schedule['class_id'];
                $bg_color = $class_colors[$schedule['class_id']] ?? '#800020';
        ?>
            <div style="display: flex; align-items: center; gap: 10px;">
                <div style="width: 30px; height: 30px; background: <?php echo $bg_color; ?>; border-radius: 4px;"></div>
                <span><strong><?php echo htmlspecialchars($schedule['class_code']); ?></strong> - <?php echo htmlspecialchars($schedule['class_name']); ?></span>
            </div>
        <?php 
            endif;
        endforeach; 
        ?>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

