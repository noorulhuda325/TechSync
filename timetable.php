<?php
/**
 * Admin - Timetable Builder
 * Create and manage master timetable (no overlapping hours)
 */

require_once '../includes/config.php';
requireRole('admin');

$page_title = 'Timetable Builder';

$conn = getDBConnection();
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_schedule'])) {
        $class_id = intval($_POST['class_id']);
        $day_of_week = $_POST['day_of_week'];
        $hour_number = intval($_POST['hour_number']);
        $room_id = intval($_POST['room_id']);
        $start_time = $_POST['start_time'];
        $end_time = $_POST['end_time'];
        
        // Check for overlapping schedule (same room, same day, same hour)
        $check = $conn->query("
            SELECT id FROM schedules 
            WHERE day_of_week = '$day_of_week' 
            AND hour_number = $hour_number 
            AND room_id = $room_id
        ");
        
        if ($check->num_rows > 0) {
            $error = "Schedule conflict! Room is already booked at this time slot.";
        } else {
            $stmt = $conn->prepare("INSERT INTO schedules (class_id, day_of_week, hour_number, room_id, start_time, end_time) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("isiss", $class_id, $day_of_week, $hour_number, $room_id, $start_time, $end_time);
            
            if ($stmt->execute()) {
                $success = "Schedule added successfully!";
            } else {
                $error = "Error: " . $stmt->error;
            }
            $stmt->close();
        }
    }
    
    if (isset($_POST['delete_schedule'])) {
        $schedule_id = intval($_POST['schedule_id']);
        $conn->query("DELETE FROM schedules WHERE id = $schedule_id");
        $success = "Schedule deleted successfully!";
    }
}

// Get all schedules with related info
$schedules = $conn->query("
    SELECT s.*, c.class_code, c.class_name, r.room_number, t.full_name as teacher_name
    FROM schedules s
    LEFT JOIN classes c ON s.class_id = c.id
    LEFT JOIN rooms r ON s.room_id = r.id
    LEFT JOIN teachers t ON c.teacher_id = t.id
    ORDER BY s.day_of_week, s.hour_number
")->fetch_all(MYSQLI_ASSOC);

// Get classes and rooms for dropdowns
$classes = $conn->query("SELECT * FROM classes ORDER BY class_name")->fetch_all(MYSQLI_ASSOC);
$rooms = $conn->query("SELECT * FROM rooms ORDER BY room_number")->fetch_all(MYSQLI_ASSOC);

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
        <h3 class="card-title">Master Timetable</h3>
        <button class="btn btn-primary" onclick="openModal('addScheduleModal')">
            <i class="fas fa-plus"></i> Add Schedule
        </button>
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
                                    <?php $schedule = $timetable[$day][$hour]; ?>
                                    <div class="timetable-cell">
                                        <strong><?php echo htmlspecialchars($schedule['class_code']); ?></strong><br>
                                        <?php echo htmlspecialchars($schedule['class_name']); ?><br>
                                        <small>Room: <?php echo htmlspecialchars($schedule['room_number'] ?? 'N/A'); ?></small><br>
                                        <small>Teacher: <?php echo htmlspecialchars($schedule['teacher_name'] ?? 'N/A'); ?></small><br>
                                        <?php if ($schedule['start_time']): ?>
                                            <small><?php echo formatTime($schedule['start_time']); ?> - <?php echo formatTime($schedule['end_time']); ?></small>
                                        <?php endif; ?>
                                        <form method="POST" style="margin-top: 5px;" onsubmit="return confirm('Delete this schedule?');">
                                            <input type="hidden" name="schedule_id" value="<?php echo $schedule['id']; ?>">
                                            <button type="submit" name="delete_schedule" class="btn btn-sm btn-danger" style="padding: 2px 6px; font-size: 0.7rem;">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <div style="color: var(--text-light); font-size: 0.85rem;">Available</div>
                                <?php endif; ?>
                            </td>
                        <?php endforeach; ?>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add Schedule Modal -->
<div class="modal" id="addScheduleModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add Schedule</h3>
            <button class="modal-close" onclick="closeModal('addScheduleModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="form-group">
                <label>Class *</label>
                <select name="class_id" required>
                    <option value="">Select Class</option>
                    <?php foreach ($classes as $class): ?>
                        <option value="<?php echo $class['id']; ?>"><?php echo htmlspecialchars($class['class_code'] . ' - ' . $class['class_name']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Day of Week *</label>
                    <select name="day_of_week" required>
                        <?php foreach ($days as $day): ?>
                            <option value="<?php echo $day; ?>"><?php echo $day; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label>Hour Number *</label>
                    <select name="hour_number" required>
                        <?php foreach ($hours as $hour): ?>
                            <option value="<?php echo $hour; ?>">Hour <?php echo $hour; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label>Room *</label>
                <select name="room_id" required>
                    <option value="">Select Room</option>
                    <?php foreach ($rooms as $room): ?>
                        <option value="<?php echo $room['id']; ?>"><?php echo htmlspecialchars($room['room_number']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Start Time</label>
                    <input type="time" name="start_time">
                </div>
                <div class="form-group">
                    <label>End Time</label>
                    <input type="time" name="end_time">
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addScheduleModal')">Cancel</button>
                <button type="submit" name="add_schedule" class="btn btn-primary">Add Schedule</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

