<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as admin
requireRole('admin');

// Add Schedule
if (isset($_POST['add_schedule'])) {
    $course_id = intval($_POST['course_id']);
    $trainer_id = cleanInput($_POST['trainer_id']);
    $start_date = cleanInput($_POST['start_date']);
    $end_date = cleanInput($_POST['end_date']);
    $start_time = cleanInput($_POST['start_time']);
    $end_time = cleanInput($_POST['end_time']);
    $days = implode(',', $_POST['days']);
    
    $stmt = $conn->prepare("INSERT INTO schedules (course_id, trainer_id, start_date, end_date, start_time, end_time, days) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("issssss", $course_id, $trainer_id, $start_date, $end_date, $start_time, $end_time, $days);
    
    if ($stmt->execute()) {
        $success = "Schedule added successfully!";
    } else {
        $error = "Error adding schedule: " . $conn->error;
    }
}

// Edit Schedule
if (isset($_POST['update_schedule'])) {
    $schedule_id = intval($_POST['schedule_id']);
    $course_id = intval($_POST['course_id']);
    $trainer_id = cleanInput($_POST['trainer_id']);
    $start_date = cleanInput($_POST['start_date']);
    $end_date = cleanInput($_POST['end_date']);
    $start_time = cleanInput($_POST['start_time']);
    $end_time = cleanInput($_POST['end_time']);
    $days = implode(',', $_POST['days']);
    
    $stmt = $conn->prepare("UPDATE schedules SET course_id=?, trainer_id=?, start_date=?, end_date=?, start_time=?, end_time=?, days=? WHERE id=?");
    $stmt->bind_param("issssssi", $course_id, $trainer_id, $start_date, $end_date, $start_time, $end_time, $days, $schedule_id);
    
    if ($stmt->execute()) {
        $success = "Schedule updated successfully!";
    } else {
        $error = "Error updating schedule: " . $conn->error;
    }
}

// Delete Schedule
if (isset($_POST['delete_schedule'])) {
    $schedule_id = intval($_POST['schedule_id']);
    
    $stmt = $conn->prepare("DELETE FROM schedules WHERE id=?");
    $stmt->bind_param("i", $schedule_id);
    
    if ($stmt->execute()) {
        $success = "Schedule deleted successfully!";
    } else {
        $error = "Error deleting schedule: " . $conn->error;
    }
}

// Load schedule data for editing if schedule_id is provided
$edit_mode = false;
$schedule_data = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM schedules WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $edit_mode = true;
        $schedule_data = $result->fetch_assoc();
    }
}

// Fetch all schedules
$schedules = $conn->query("SELECT s.*, c.course_name, 
                          CONCAT(t.first_name, ' ', t.last_name) AS trainer_name 
                          FROM schedules s 
                          LEFT JOIN courses c ON s.course_id = c.id 
                          LEFT JOIN trainers t ON s.trainer_id = t.trainer_id 
                          ORDER BY s.start_date DESC");

// Fetch courses for dropdown
$courses = $conn->query("SELECT id, course_name FROM courses ORDER BY course_name");

// Fetch trainers for dropdown
$trainers = $conn->query("SELECT trainer_id, first_name, last_name FROM trainers ORDER BY first_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management - Educational Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .form-section {
            max-width: 600px;
            margin: 0 auto 30px;
            background-color: #f8f9fa;
            padding: 25px;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }
        .form-section h3 {
            margin-bottom: 20px;
            color: #2c3e50;
            border-bottom: 2px solid #3498db;
            padding-bottom: 10px;
        }
        .checkbox-group {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-top: 10px;
        }
        .checkbox-group label {
            display: flex;
            align-items: center;
            margin-right: 10px;
            font-weight: normal;
        }
        .checkbox-group input[type="checkbox"] {
            margin-right: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['name']); ?>&background=3498db&color=fff&size=128" alt="Profile Picture">
                <h3><?php echo htmlspecialchars($_SESSION['name']); ?></h3>
                <p>Administrator</p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="student.php"><i class="fas fa-user-graduate"></i> Student Management</a></li>
                    <li><a href="course_management.php"><i class="fas fa-book"></i> Course Management</a></li>
                    <li><a href="schedule_management.php" class="active"><i class="fas fa-calendar-alt"></i> Schedule Management</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1><?php echo $edit_mode ? 'Edit Schedule' : 'Schedule Management'; ?></h1>
            </header>
            
            <div id="content-area" class="fade-in">
                <div class="content-box">
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-section">
                        <h3><?php echo $edit_mode ? 'Edit Schedule' : 'Add New Schedule'; ?></h3>
                        <form method="POST" action="">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="schedule_id" value="<?php echo $schedule_data['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="course_id">Course</label>
                                <select name="course_id" id="course_id" class="form-control" required>
                                    <option value="">-- Select Course --</option>
                                    <?php while ($course = $courses->fetch_assoc()): ?>
                                        <option value="<?php echo $course['id']; ?>" 
                                                <?php echo ($edit_mode && $schedule_data['course_id'] == $course['id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($course['course_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="trainer_id">Trainer</label>
                                <select name="trainer_id" id="trainer_id" class="form-control" required>
                                    <option value="">-- Select Trainer --</option>
                                    <?php while ($trainer = $trainers->fetch_assoc()): ?>
                                        <option value="<?php echo $trainer['trainer_id']; ?>" 
                                                <?php echo ($edit_mode && $schedule_data['trainer_id'] == $trainer['trainer_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label for="start_date">Start Date</label>
                                <input type="date" name="start_date" id="start_date" class="form-control" required
                                       value="<?php echo $edit_mode ? $schedule_data['start_date'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="end_date">End Date</label>
                                <input type="date" name="end_date" id="end_date" class="form-control" required
                                       value="<?php echo $edit_mode ? $schedule_data['end_date'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="start_time">Start Time</label>
                                <input type="time" name="start_time" id="start_time" class="form-control" required
                                       value="<?php echo $edit_mode ? $schedule_data['start_time'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="end_time">End Time</label>
                                <input type="time" name="end_time" id="end_time" class="form-control" required
                                       value="<?php echo $edit_mode ? $schedule_data['end_time'] : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label>Days</label>
                                <div class="checkbox-group">
                                    <?php 
                                    $days_array = $edit_mode ? explode(',', $schedule_data['days']) : [];
                                    $days_options = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                    foreach ($days_options as $day): 
                                    ?>
                                        <label>
                                            <input type="checkbox" name="days[]" value="<?php echo $day; ?>" 
                                                <?php echo ($edit_mode && in_array($day, $days_array)) ? 'checked' : ''; ?>>
                                            <?php echo $day; ?>
                                        </label>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            
                            <div class="form-group">
                                <?php if ($edit_mode): ?>
                                    <button type="submit" name="update_schedule" class="btn btn-success">Update Schedule</button>
                                    <a href="schedule_management.php" class="btn" style="margin-left: 10px;">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_schedule" class="btn btn-success">Add Schedule</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    
                    <h2>Existing Schedules</h2>
                    <div class="table-container" style="overflow-x: auto;">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Course</th>
                                    <th>Trainer</th>
                                    <th>Period</th>
                                    <th>Time</th>
                                    <th>Days</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($schedules->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="7" style="text-align: center;">No schedules found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($row = $schedules->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                            <td><?php echo htmlspecialchars($row['trainer_name']); ?></td>
                                            <td>
                                                <?php 
                                                    echo date('M d, Y', strtotime($row['start_date'])) . ' - ' . 
                                                         date('M d, Y', strtotime($row['end_date'])); 
                                                ?>
                                            </td>
                                            <td>
                                                <?php 
                                                    echo date('h:i A', strtotime($row['start_time'])) . ' - ' . 
                                                         date('h:i A', strtotime($row['end_time'])); 
                                                ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($row['days']); ?></td>
                                            <td>
                                                <a href="schedule_management.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="schedule_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete_schedule" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Are you sure you want to delete this schedule?')">
                                                        <i class="fas fa-trash"></i> Delete
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <a href="dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
