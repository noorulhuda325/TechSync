<?php
/**
 * Student - Calendar & Events
 * View events and exam dates
 */

require_once '../includes/config.php';
requireRole('student');

$page_title = 'Calendar & Events';

$conn = getDBConnection();

// Get filter parameters
$event_type = isset($_GET['type']) ? $_GET['type'] : 'all';
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d');

// Build query
$where = "WHERE event_date >= '$start_date'";
if ($event_type !== 'all') {
    $where .= " AND event_type = '$event_type'";
}

// Get events
$events = $conn->query("
    SELECT e.*, a.full_name as created_by_name 
    FROM events e 
    LEFT JOIN admins a ON e.created_by = a.id
    $where
    ORDER BY e.event_date ASC, e.event_time ASC
")->fetch_all(MYSQLI_ASSOC);

// Group events by date
$events_by_date = [];
foreach ($events as $event) {
    $date = $event['event_date'];
    if (!isset($events_by_date[$date])) {
        $events_by_date[$date] = [];
    }
    $events_by_date[$date][] = $event;
}

include '../includes/header.php';
?>

<div class="card">
    <div class="card-header">
        <h3 class="card-title">Events Calendar</h3>
    </div>
    
    <form method="GET" style="margin-bottom: 20px; padding: 20px; background: var(--bg-color); border-radius: 6px;">
        <div class="form-row">
            <div class="form-group">
                <label>Event Type</label>
                <select name="type" onchange="this.form.submit()">
                    <option value="all" <?php echo $event_type === 'all' ? 'selected' : ''; ?>>All Events</option>
                    <option value="exam" <?php echo $event_type === 'exam' ? 'selected' : ''; ?>>Exams</option>
                    <option value="meeting" <?php echo $event_type === 'meeting' ? 'selected' : ''; ?>>Meetings</option>
                    <option value="holiday" <?php echo $event_type === 'holiday' ? 'selected' : ''; ?>>Holidays</option>
                    <option value="other" <?php echo $event_type === 'other' ? 'selected' : ''; ?>>Other</option>
                </select>
            </div>
            <div class="form-group">
                <label>From Date</label>
                <input type="date" name="start_date" value="<?php echo $start_date; ?>" onchange="this.form.submit()">
            </div>
        </div>
    </form>
    
    <?php if (count($events) > 0): ?>
        <div style="padding: 20px;">
            <?php foreach ($events_by_date as $date => $date_events): ?>
                <div style="margin-bottom: 30px;">
                    <h4 style="color: var(--primary-color); margin-bottom: 15px; padding-bottom: 10px; border-bottom: 2px solid var(--border-color);">
                        <i class="fas fa-calendar-day"></i> <?php echo formatDate($date); ?>
                    </h4>
                    <div style="display: grid; gap: 15px;">
                        <?php foreach ($date_events as $event): ?>
                            <div class="card" style="padding: 20px;">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                                    <div>
                                        <h5 style="margin-bottom: 5px; color: var(--text-color);">
                                            <?php echo htmlspecialchars($event['title']); ?>
                                        </h5>
                                        <?php if ($event['description']): ?>
                                            <p style="color: var(--text-light); margin-bottom: 10px;">
                                                <?php echo htmlspecialchars($event['description']); ?>
                                            </p>
                                        <?php endif; ?>
                                        <div style="display: flex; gap: 15px; align-items: center;">
                                            <?php if ($event['event_time']): ?>
                                                <small style="color: var(--text-light);">
                                                    <i class="fas fa-clock"></i> <?php echo formatTime($event['event_time']); ?>
                                                </small>
                                            <?php endif; ?>
                                            <span class="badge badge-<?php 
                                                echo $event['event_type'] === 'exam' ? 'danger' : 
                                                    ($event['event_type'] === 'meeting' ? 'info' : 
                                                    ($event['event_type'] === 'holiday' ? 'warning' : 'primary')); 
                                            ?>">
                                                <?php echo ucfirst($event['event_type']); ?>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php else: ?>
        <div style="padding: 40px; text-align: center; color: var(--text-light);">
            <i class="fas fa-calendar-times" style="font-size: 3rem; margin-bottom: 20px; opacity: 0.5;"></i>
            <p>No events found for the selected criteria.</p>
        </div>
    <?php endif; ?>
</div>

<?php include '../includes/footer.php'; ?>

