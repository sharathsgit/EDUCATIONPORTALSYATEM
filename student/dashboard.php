<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as student
requireRole('student');

$student_id = $_SESSION['user_id'];

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Fetch enrolled courses count
$stmt = $conn->prepare("SELECT COUNT(*) AS enrolled_courses FROM enrollments WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$enrolled_courses = $row['enrolled_courses'];

// For demo purposes, set some sample metrics
$completed_lessons = rand(3, 15);
$pending_assignments = rand(0, 5);
$hours_spent = rand(10, 40);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - Educational Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Welcome, <?php echo htmlspecialchars($student['first_name']); ?>!</h1>
            </header>
            
            <section class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-book"></i>
                    <h2><?php echo $enrolled_courses; ?></h2>
                    <p>Enrolled Courses</p>
                </div>
                <div class="card">
                    <i class="fas fa-calendar-check"></i>
                    <h2><?php echo $completed_lessons; ?></h2>
                    <p>Completed Lessons</p>
                </div>
                <div class="card">
                    <i class="fas fa-tasks"></i>
                    <h2><?php echo $pending_assignments; ?></h2>
                    <p>Pending Assignments</p>
                </div>
                <div class="card">
                    <i class="fas fa-clock"></i>
                    <h2><?php echo $hours_spent; ?></h2>
                    <p>Hours Spent</p>
                </div>
            </section>
            
            <div id="content-area" class="fade-in">
                <div class="content-box">
                    <h2>Recent Activity</h2>
                    <p>Welcome to your student dashboard! Here you can track your courses, schedules, and overall progress.</p>
                    
                    <div style="margin-top: 30px;">
                        <h3>Quick Actions</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px;">
                            <a href="courses.php" class="btn">
                                <i class="fas fa-book"></i> Browse Courses
                            </a>
                            <a href="profile.php" class="btn">
                                <i class="fas fa-user-edit"></i> Update Profile
                            </a>
                            <a href="schedule.php" class="btn">
                                <i class="fas fa-calendar-day"></i> View Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
