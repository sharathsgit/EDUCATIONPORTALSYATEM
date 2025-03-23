<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Handle Add Schedule
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_schedule'])) {
    $trainer_name = $_POST['trainer_name'];
    $expert_course = $_POST['expert_course'];
    $available_timing = $_POST['available_timing'];

    $stmt = $conn->prepare("INSERT INTO schedules (trainer_name, expert_course, available_timing) VALUES (?, ?, ?)");
    $stmt->bind_param("sss", $trainer_name, $expert_course, $available_timing);
    $stmt->execute();
    $stmt->close();
    header("Location: schedule_management.php");
    exit();
}

// Fetch schedules
$schedules = $conn->query("SELECT * FROM schedules");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Schedule Management</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <style>
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
        }
        .styled-table th, .styled-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .styled-table th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Schedule Management</h2>
        
        <h3>Add Trainer Schedule</h3>
        <form method="POST">
            <input type="text" name="trainer_name" placeholder="Trainer Name" required>
            <input type="text" name="expert_course" placeholder="Expert Course" required>
            <input type="text" name="available_timing" placeholder="Available Timing" required>
            <button type="submit" name="add_schedule">Add Schedule</button>
        </form>
        
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Trainer Name</th>
                    <th>Expert Course</th>
                    <th>Available Timing</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $schedules->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['trainer_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['expert_course']); ?></td>
                        <td><?php echo htmlspecialchars($row['available_timing']); ?></td>
                        <td>
                            <form method="POST" action="edit_schedule.php" style="display:inline;">
                                <input type="hidden" name="schedule_id" value="<?php echo $row['id']; ?>">
                                <button type="submit">Edit</button>
                            </form>
                            <form method="POST" action="delete_schedule.php" style="display:inline;">
                                <input type="hidden" name="schedule_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" onclick="return confirm('Are you sure?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
</body>
</html>
