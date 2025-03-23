<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as admin
requireRole('admin');

// Fetch dashboard data
// Total students count
$stmt = $conn->prepare("SELECT COUNT(*) AS total_students FROM students");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_students = $row['total_students'];

// Total courses count
$stmt = $conn->prepare("SELECT COUNT(*) AS total_courses FROM courses");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_courses = $row['total_courses'];

// Total trainers count
$stmt = $conn->prepare("SELECT COUNT(*) AS total_trainers FROM trainers");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_trainers = $row['total_trainers'];

// Total scheduled classes count
$stmt = $conn->prepare("SELECT COUNT(*) AS total_schedules FROM schedules");
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_schedules = $row['total_schedules'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Educational Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="student.php"><i class="fas fa-user-graduate"></i> Student Management</a></li>
                    <li><a href="course_management.php"><i class="fas fa-book"></i> Course Management</a></li>
                    <li><a href="schedule_management.php"><i class="fas fa-calendar-alt"></i> Schedule Management</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Welcome, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h1>
            </header>
            
            <section class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-user-graduate"></i>
                    <h2><?php echo $total_students; ?></h2>
                    <a href="student.php">Total Students</a>
                </div>
                <div class="card">
                    <i class="fas fa-book"></i>
                    <h2><?php echo $total_courses; ?></h2>
                    <a href="course_management.php">Courses Available</a>
                </div>
                <div class="card">
                    <i class="fas fa-calendar-alt"></i>
                    <h2><?php echo $total_schedules; ?></h2>
                    <a href="schedule_management.php">Scheduled Classes</a>
                </div>
                <div class="card">
                    <i class="fas fa-chalkboard-teacher"></i>
                    <h2><?php echo $total_trainers; ?></h2>
                    <p>Active Trainers</p>
                </div>
            </section>
            
            <div id="content-area" class="fade-in">
                <div class="content-box">
                    <h2>Recent Activities</h2>
                    <p>Welcome to the admin dashboard! From here you can manage students, courses, and schedules for the entire educational management system.</p>
                    
                    <div class="quick-actions" style="margin-top: 30px;">
                        <h3>Quick Actions</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px;">
                            <a href="student.php" class="btn">
                                <i class="fas fa-user-plus"></i> Manage Students
                            </a>
                            <a href="course_management.php" class="btn">
                                <i class="fas fa-plus-circle"></i> Add New Course
                            </a>
                            <a href="schedule_management.php" class="btn">
                                <i class="fas fa-calendar-plus"></i> Create Schedule
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
    
    <script>
        function loadPage(page) {
            var xhr = new XMLHttpRequest();
            xhr.open("GET", page, true);
            xhr.onreadystatechange = function () {
                if (xhr.readyState == 4 && xhr.status == 200) {
                    var contentArea = document.getElementById("content-area");
                    contentArea.classList.remove("fade-in");
                    setTimeout(() => {
                        contentArea.innerHTML = xhr.responseText;
                        contentArea.classList.add("fade-in");
                    }, 100);
                }
            };
            xhr.send();
        }
    </script>
</body>
</html>
