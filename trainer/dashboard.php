<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as trainer
requireRole('trainer');

$trainer_id = $_SESSION['user_id'];

// Fetch trainer data
$stmt = $conn->prepare("SELECT * FROM trainers WHERE trainer_id = ?");
$stmt->bind_param("s", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer = $result->fetch_assoc();

// Count assigned courses
$stmt = $conn->prepare("SELECT COUNT(*) AS total_courses FROM courses WHERE trainer_id = ?");
$stmt->bind_param("s", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_courses = $row['total_courses'];

// Count scheduled classes
$stmt = $conn->prepare("SELECT COUNT(*) AS total_schedules FROM schedules WHERE trainer_id = ?");
$stmt->bind_param("s", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_schedules = $row['total_schedules'];

// Count enrolled students in trainer's courses
$stmt = $conn->prepare("SELECT COUNT(DISTINCT e.student_id) AS total_students 
                      FROM enrollments e 
                      JOIN courses c ON e.course_id = c.id 
                      WHERE c.trainer_id = ?");
$stmt->bind_param("s", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$total_students = $row['total_students'];

// For demo purposes, show average hours per week
$hours_per_week = rand(5, 20);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trainer Dashboard - Educational Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="profile">
                <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($trainer['first_name'] . ' ' . $trainer['last_name']); ?>&background=3498db&color=fff&size=128" alt="Profile Picture">
                <h3><?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></h3>
                <p>Trainer</p>
            </div>
            <nav>
                <ul>
                    <li><a href="dashboard.php" class="active"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                    <li><a href="my_schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Welcome, <?php echo htmlspecialchars($trainer['first_name']); ?>!</h1>
            </header>
            
            <section class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-book"></i>
                    <h2><?php echo $total_courses; ?></h2>
                    <p>Assigned Courses</p>
                </div>
                <div class="card">
                    <i class="fas fa-user-graduate"></i>
                    <h2><?php echo $total_students; ?></h2>
                    <p>Enrolled Students</p>
                </div>
                <div class="card">
                    <i class="fas fa-calendar-alt"></i>
                    <h2><?php echo $total_schedules; ?></h2>
                    <p>Scheduled Classes</p>
                </div>
                <div class="card">
                    <i class="fas fa-clock"></i>
                    <h2><?php echo $hours_per_week; ?></h2>
                    <p>Hours per Week</p>
                </div>
            </section>
            
            <div id="content-area" class="fade-in">
                <div class="content-box">
                    <h2>Upcoming Classes</h2>
                    
                    <?php
                    // Fetch upcoming schedule for today and next few days
                    $current_date = date('Y-m-d');
                    $current_day = date('l');
                    
                    $stmt = $conn->prepare("SELECT s.*, c.course_name 
                                          FROM schedules s 
                                          JOIN courses c ON s.course_id = c.id 
                                          WHERE s.trainer_id = ? 
                                          AND s.end_date >= ? 
                                          ORDER BY s.start_time 
                                          LIMIT 5");
                    $stmt->bind_param("ss", $trainer_id, $current_date);
                    $stmt->execute();
                    $upcoming_classes = $stmt->get_result();
                    
                    if ($upcoming_classes->num_rows > 0) {
                        echo '<div style="margin-top: 20px;">';
                        
                        while ($class = $upcoming_classes->fetch_assoc()) {
                            $days_array = explode(',', $class['days']);
                            $is_today = in_array($current_day, $days_array);
                            
                            echo '<div style="background-color: ' . ($is_today ? '#e8f0fd' : '#f8f9fa') . '; padding: 15px; border-radius: 8px; margin-bottom: 15px; border-left: 4px solid ' . ($is_today ? '#3498db' : '#bdc3c7') . ';">';
                            echo '<div style="display: flex; justify-content: space-between; align-items: center;">';
                            echo '<h3 style="margin: 0; color: #2c3e50;">' . htmlspecialchars($class['course_name']) . '</h3>';
                            echo '<div style="color: ' . ($is_today ? '#3498db' : '#7f8c8d') . ';">' . date('h:i A', strtotime($class['start_time'])) . ' - ' . date('h:i A', strtotime($class['end_time'])) . '</div>';
                            echo '</div>';
                            
                            echo '<div style="margin-top: 10px; display: flex; gap: 10px; flex-wrap: wrap;">';
                            foreach ($days_array as $day) {
                                $is_current_day = ($day === $current_day);
                                echo '<span style="padding: 3px 10px; border-radius: 15px; font-size: 12px; background-color: ' . ($is_current_day ? '#3498db' : '#f0f0f0') . '; color: ' . ($is_current_day ? 'white' : '#7f8c8d') . ';">' . $day . '</span>';
                            }
                            echo '</div>';
                            
                            echo '</div>';
                        }
                        
                        echo '</div>';
                    } else {
                        echo '<p>You have no upcoming classes scheduled.</p>';
                    }
                    ?>
                    
                    <div style="margin-top: 30px;">
                        <h3>Quick Actions</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 15px; margin-top: 15px;">
                            <a href="my_courses.php" class="btn">
                                <i class="fas fa-book"></i> View My Courses
                            </a>
                            <a href="my_schedule.php" class="btn">
                                <i class="fas fa-calendar-alt"></i> Check Schedule
                            </a>
                            <a href="profile.php" class="btn">
                                <i class="fas fa-user-edit"></i> Update Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
