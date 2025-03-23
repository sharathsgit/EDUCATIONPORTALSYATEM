<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as admin
requireRole('admin');

// Add Course
if (isset($_POST['add_course'])) {
    $course_name = cleanInput($_POST['course_name']);
    $description = cleanInput($_POST['description']);
    $price = floatval($_POST['price']);
    $trainer_id = !empty($_POST['trainer_id']) ? cleanInput($_POST['trainer_id']) : null;
    
    $stmt = $conn->prepare("INSERT INTO courses (course_name, description, price, trainer_id) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssds", $course_name, $description, $price, $trainer_id);
    
    if ($stmt->execute()) {
        $success = "Course added successfully!";
    } else {
        $error = "Error adding course: " . $conn->error;
    }
}

// Edit Course
if (isset($_POST['update_course'])) {
    $course_id = intval($_POST['course_id']);
    $course_name = cleanInput($_POST['course_name']);
    $description = cleanInput($_POST['description']);
    $price = floatval($_POST['price']);
    $trainer_id = !empty($_POST['trainer_id']) ? cleanInput($_POST['trainer_id']) : null;
    
    $stmt = $conn->prepare("UPDATE courses SET course_name=?, description=?, price=?, trainer_id=? WHERE id=?");
    $stmt->bind_param("ssdsi", $course_name, $description, $price, $trainer_id, $course_id);
    
    if ($stmt->execute()) {
        $success = "Course updated successfully!";
    } else {
        $error = "Error updating course: " . $conn->error;
    }
}

// Delete Course
if (isset($_POST['delete_course'])) {
    $course_id = intval($_POST['course_id']);
    
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
    $stmt->bind_param("i", $course_id);
    
    if ($stmt->execute()) {
        $success = "Course deleted successfully!";
    } else {
        $error = "Error deleting course: " . $conn->error;
    }
}

// Load course data for editing if course_id is provided
$edit_mode = false;
$course_data = null;

if (isset($_GET['edit']) && is_numeric($_GET['edit'])) {
    $edit_id = intval($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM courses WHERE id = ?");
    $stmt->bind_param("i", $edit_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $edit_mode = true;
        $course_data = $result->fetch_assoc();
    }
}

// Fetch all courses
$courses = $conn->query("SELECT c.*, t.first_name, t.last_name 
                         FROM courses c 
                         LEFT JOIN trainers t ON c.trainer_id = t.trainer_id 
                         ORDER BY c.created_at DESC");

// Fetch trainers for dropdown
$trainers = $conn->query("SELECT trainer_id, first_name, last_name FROM trainers ORDER BY first_name");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Course Management - Educational Management System</title>
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
                    <li><a href="course_management.php" class="active"><i class="fas fa-book"></i> Course Management</a></li>
                    <li><a href="schedule_management.php"><i class="fas fa-calendar-alt"></i> Schedule Management</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1><?php echo $edit_mode ? 'Edit Course' : 'Course Management'; ?></h1>
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
                        <h3><?php echo $edit_mode ? 'Edit Course' : 'Add New Course'; ?></h3>
                        <form method="POST" action="">
                            <?php if ($edit_mode): ?>
                                <input type="hidden" name="course_id" value="<?php echo $course_data['id']; ?>">
                            <?php endif; ?>
                            
                            <div class="form-group">
                                <label for="course_name">Course Name</label>
                                <input type="text" name="course_name" id="course_name" class="form-control" 
                                       placeholder="Enter course name" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($course_data['course_name']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="description">Course Description</label>
                                <textarea name="description" id="description" class="form-control" 
                                          placeholder="Enter course description" rows="4" required><?php echo $edit_mode ? htmlspecialchars($course_data['description']) : ''; ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="price">Course Price ($)</label>
                                <input type="number" name="price" id="price" class="form-control" 
                                       placeholder="Enter price" step="0.01" min="0" required
                                       value="<?php echo $edit_mode ? htmlspecialchars($course_data['price']) : ''; ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="trainer_id">Assign Trainer (Optional)</label>
                                <select name="trainer_id" id="trainer_id" class="form-control">
                                    <option value="">-- Select Trainer --</option>
                                    <?php while ($trainer = $trainers->fetch_assoc()): ?>
                                        <option value="<?php echo $trainer['trainer_id']; ?>" 
                                                <?php echo ($edit_mode && $course_data['trainer_id'] == $trainer['trainer_id']) ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <?php if ($edit_mode): ?>
                                    <button type="submit" name="update_course" class="btn btn-success">Update Course</button>
                                    <a href="course_management.php" class="btn" style="margin-left: 10px;">Cancel</a>
                                <?php else: ?>
                                    <button type="submit" name="add_course" class="btn btn-success">Add Course</button>
                                <?php endif; ?>
                            </div>
                        </form>
                    </div>
                    
                    <h2>Existing Courses</h2>
                    <div class="table-container" style="overflow-x: auto;">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Course Name</th>
                                    <th>Description</th>
                                    <th>Price</th>
                                    <th>Assigned Trainer</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($courses->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="6" style="text-align: center;">No courses found.</td>
                                    </tr>
                                <?php else: ?>
                                    <?php while ($row = $courses->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id']; ?></td>
                                            <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                                            <td><?php echo substr(htmlspecialchars($row['description']), 0, 100) . (strlen($row['description']) > 100 ? '...' : ''); ?></td>
                                            <td>$<?php echo number_format($row['price'], 2); ?></td>
                                            <td>
                                                <?php 
                                                    echo !empty($row['first_name']) 
                                                        ? htmlspecialchars($row['first_name'] . ' ' . $row['last_name']) 
                                                        : '—';
                                                ?>
                                            </td>
                                            <td>
                                                <a href="course_management.php?edit=<?php echo $row['id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">
                                                    <i class="fas fa-edit"></i> Edit
                                                </a>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                                                    <button type="submit" name="delete_course" class="btn btn-danger" style="padding: 5px 10px; font-size: 12px;" onclick="return confirm('Are you sure you want to delete this course?')">
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
