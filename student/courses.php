<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as student
requireRole('student');

$student_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Handle course enrollment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['enroll_course'])) {
    $course_id = intval($_POST['course_id']);
    
    // Check if already enrolled
    $stmt = $conn->prepare("SELECT * FROM enrollments WHERE student_id = ? AND course_id = ?");
    $stmt->bind_param("si", $student_id, $course_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $error = "You are already enrolled in this course.";
    } else {
        $stmt = $conn->prepare("INSERT INTO enrollments (student_id, course_id) VALUES (?, ?)");
        $stmt->bind_param("si", $student_id, $course_id);
        
        if ($stmt->execute()) {
            $success = "Successfully enrolled in the course!";
        } else {
            $error = "Error enrolling in the course: " . $conn->error;
        }
    }
}

// Get enrolled courses
$stmt = $conn->prepare("SELECT c.*, e.enrollment_date, e.status, 
                        CONCAT(t.first_name, ' ', t.last_name) AS trainer_name 
                        FROM enrollments e 
                        JOIN courses c ON e.course_id = c.id 
                        LEFT JOIN trainers t ON c.trainer_id = t.trainer_id 
                        WHERE e.student_id = ? 
                        ORDER BY e.enrollment_date DESC");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();

// Get available courses (not enrolled)
$stmt = $conn->prepare("SELECT c.*, CONCAT(t.first_name, ' ', t.last_name) AS trainer_name 
                        FROM courses c 
                        LEFT JOIN trainers t ON c.trainer_id = t.trainer_id 
                        WHERE c.id NOT IN (
                            SELECT course_id FROM enrollments WHERE student_id = ?
                        ) 
                        ORDER BY c.created_at DESC");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$available_courses = $stmt->get_result();

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Courses - Educational Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .course-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            display: flex;
            flex-direction: column;
        }
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .course-title {
            font-size: 18px;
            font-weight: bold;
            color: #2c3e50;
        }
        .course-price {
            font-weight: bold;
            color: #27ae60;
        }
        .course-content {
            flex: 1;
        }
        .course-description {
            color: #7f8c8d;
            margin-bottom: 15px;
        }
        .course-meta {
            display: flex;
            justify-content: space-between;
            color: #95a5a6;
            font-size: 14px;
            margin-top: 15px;
        }
        .course-tabs {
            display: flex;
            gap: 20px;
            margin-bottom: 20px;
        }
        .course-tab {
            padding: 10px 20px;
            background-color: #f5f5f5;
            border-radius: 5px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .course-tab.active {
            background-color: #3498db;
            color: white;
        }
        .course-container {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-active {
            background-color: #d4f5e2;
            color: #27ae60;
        }
        .status-completed {
            background-color: #e8f0fd;
            color: #3498db;
        }
        .status-dropped {
            background-color: #fde8e8;
            color: #e74c3c;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($student['first_name'] . ' ' . $student['last_name']); ?>&background=3498db&color=fff&size=128" alt="Profile Picture">
                <h3><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h3>
                <p>Student</p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="courses.php" class="active"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>My Courses</h1>
            </header>
            
            <div id="content-area" class="fade-in">
                <div class="content-box">
                    <?php if (!empty($success)): ?>
                        <div class="alert alert-success" style="background-color: #d4edda; color: #155724; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                            <?php echo $success; ?>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($error)): ?>
                        <div class="alert alert-danger" style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 20px; border-radius: 4px;">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>
                    
                    <div class="course-tabs">
                        <div class="course-tab active" id="tab-enrolled" onclick="switchTab('enrolled')">
                            <i class="fas fa-graduation-cap"></i> My Enrolled Courses
                        </div>
                        <div class="course-tab" id="tab-available" onclick="switchTab('available')">
                            <i class="fas fa-list"></i> Available Courses
                        </div>
                    </div>
                    
                    <div id="enrolled-courses">
                        <h2>My Enrolled Courses</h2>
                        
                        <?php if ($enrolled_courses->num_rows === 0): ?>
                            <p>You are not enrolled in any courses yet. Check out the available courses to enroll.</p>
                        <?php else: ?>
                            <div class="course-container">
                                <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
                                    <div class="course-card">
                                        <div class="course-header">
                                            <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                            <span class="status-badge status-<?php echo $course['status']; ?>">
                                                <?php echo ucfirst($course['status']); ?>
                                            </span>
                                        </div>
                                        <div class="course-content">
                                            <div class="course-description">
                                                <?php echo substr(htmlspecialchars($course['description']), 0, 120) . (strlen($course['description']) > 120 ? '...' : ''); ?>
                                            </div>
                                            <div class="course-meta">
                                                <div>
                                                    <i class="fas fa-chalkboard-teacher"></i> 
                                                    <?php echo !empty($course['trainer_name']) ? htmlspecialchars($course['trainer_name']) : 'No trainer assigned'; ?>
                                                </div>
                                                <div class="course-price">$<?php echo number_format($course['price'], 2); ?></div>
                                            </div>
                                        </div>
                                        <div style="margin-top: 15px; text-align: right;">
                                            <small>Enrolled on: <?php echo date('M d, Y', strtotime($course['enrollment_date'])); ?></small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div id="available-courses" style="display: none;">
                        <h2>Available Courses</h2>
                        
                        <?php if ($available_courses->num_rows === 0): ?>
                            <p>There are no more courses available for enrollment at this time.</p>
                        <?php else: ?>
                            <div class="course-container">
                                <?php while ($course = $available_courses->fetch_assoc()): ?>
                                    <div class="course-card">
                                        <div class="course-header">
                                            <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                            <div class="course-price">$<?php echo number_format($course['price'], 2); ?></div>
                                        </div>
                                        <div class="course-content">
                                            <div class="course-description">
                                                <?php echo substr(htmlspecialchars($course['description']), 0, 120) . (strlen($course['description']) > 120 ? '...' : ''); ?>
                                            </div>
                                            <div class="course-meta">
                                                <div>
                                                    <i class="fas fa-chalkboard-teacher"></i> 
                                                    <?php echo !empty($course['trainer_name']) ? htmlspecialchars($course['trainer_name']) : 'No trainer assigned'; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div style="margin-top: 15px;">
                                            <form method="POST" action="">
                                                <input type="hidden" name="course_id" value="<?php echo $course['id']; ?>">
                                                <button type="submit" name="enroll_course" class="btn btn-success" style="width: 100%;">
                                                    <i class="fas fa-plus-circle"></i> Enroll Now
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <a href="dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function switchTab(tabName) {
            // Hide all tabs
            document.getElementById('enrolled-courses').style.display = 'none';
            document.getElementById('available-courses').style.display = 'none';
            
            // Remove active class from all tabs
            document.getElementById('tab-enrolled').classList.remove('active');
            document.getElementById('tab-available').classList.remove('active');
            
            // Show selected tab
            document.getElementById(tabName + '-courses').style.display = 'block';
            document.getElementById('tab-' + tabName).classList.add('active');
        }
    </script>
</body>
</html>
