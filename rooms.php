<?php
/**
 * Admin - Manage Rooms
 * CRUD operations for rooms
 */

require_once '../includes/config.php';
requireRole('admin');

$page_title = 'Manage Rooms';

$conn = getDBConnection();
$error = '';
$success = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['add_room'])) {
        $room_number = sanitizeInput($_POST['room_number']);
        $building = sanitizeInput($_POST['building']);
        $capacity = intval($_POST['capacity']);
        $room_type = sanitizeInput($_POST['room_type']);
        
        $stmt = $conn->prepare("INSERT INTO rooms (room_number, building, capacity, room_type) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("ssis", $room_number, $building, $capacity, $room_type);
        
        if ($stmt->execute()) {
            $success = "Room created successfully!";
        } else {
            $error = "Error: " . $stmt->error;
        }
        $stmt->close();
    }
    
    if (isset($_POST['delete_room'])) {
        $room_id = intval($_POST['room_id']);
        $conn->query("DELETE FROM rooms WHERE id = $room_id");
        $success = "Room deleted successfully!";
    }
}

// Get all rooms
$rooms = $conn->query("SELECT * FROM rooms ORDER BY building, room_number")->fetch_all(MYSQLI_ASSOC);

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
        <h3 class="card-title">Rooms List</h3>
        <button class="btn btn-primary" onclick="openModal('addRoomModal')">
            <i class="fas fa-plus"></i> Add New Room
        </button>
    </div>
    
    <div class="table-container">
        <table class="table">
            <thead>
                <tr>
                    <th>Room Number</th>
                    <th>Building</th>
                    <th>Capacity</th>
                    <th>Room Type</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rooms as $room): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($room['room_number']); ?></td>
                        <td><?php echo htmlspecialchars($room['building'] ?? 'N/A'); ?></td>
                        <td><?php echo $room['capacity'] ?? 'N/A'; ?></td>
                        <td><?php echo htmlspecialchars($room['room_type'] ?? 'N/A'); ?></td>
                        <td>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure?');">
                                <input type="hidden" name="room_id" value="<?php echo $room['id']; ?>">
                                <button type="submit" name="delete_room" class="btn btn-sm btn-danger">
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

<!-- Add Room Modal -->
<div class="modal" id="addRoomModal">
    <div class="modal-content">
        <div class="modal-header">
            <h3 class="modal-title">Add New Room</h3>
            <button class="modal-close" onclick="closeModal('addRoomModal')">&times;</button>
        </div>
        <form method="POST">
            <div class="form-row">
                <div class="form-group">
                    <label>Room Number *</label>
                    <input type="text" name="room_number" required>
                </div>
                <div class="form-group">
                    <label>Building</label>
                    <input type="text" name="building">
                </div>
            </div>
            <div class="form-row">
                <div class="form-group">
                    <label>Capacity</label>
                    <input type="number" name="capacity" min="1">
                </div>
                <div class="form-group">
                    <label>Room Type</label>
                    <select name="room_type">
                        <option value="">Select Type</option>
                        <option value="Lecture Hall">Lecture Hall</option>
                        <option value="Classroom">Classroom</option>
                        <option value="Laboratory">Laboratory</option>
                        <option value="Computer Lab">Computer Lab</option>
                        <option value="Seminar Room">Seminar Room</option>
                    </select>
                </div>
            </div>
            <div style="display: flex; gap: 10px; justify-content: flex-end;">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addRoomModal')">Cancel</button>
                <button type="submit" name="add_room" class="btn btn-primary">Create Room</button>
            </div>
        </form>
    </div>
</div>

<?php include '../includes/footer.php'; ?>

