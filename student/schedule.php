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

// Fetch schedules for enrolled courses
$stmt = $conn->prepare("SELECT s.*, c.course_name, 
                        CONCAT(t.first_name, ' ', t.last_name) AS trainer_name 
                        FROM schedules s 
                        JOIN courses c ON s.course_id = c.id 
                        LEFT JOIN trainers t ON s.trainer_id = t.trainer_id 
                        JOIN enrollments e ON e.course_id = c.id 
                        WHERE e.student_id = ? AND e.status = 'active' 
                        ORDER BY s.start_date, s.start_time");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$schedules = $stmt->get_result();

// Get current day of week
$current_day = date('l');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Schedule - Educational Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .schedule-card {
            background-color: white;
            border-radius: 8px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .schedule-header {
            padding: 15px 20px;
            background-color: #3498db;
            color: white;
            font-weight: bold;
        }
        .schedule-body {
            padding: 20px;
        }
        .schedule-time {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            color: #2c3e50;
            font-weight: bold;
        }
        .schedule-days {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 15px;
        }
        .day-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            background-color: #f5f5f5;
        }
        .day-badge.active {
            background-color: #2ecc71;
            color: white;
        }
        .schedule-trainer {
            color: #7f8c8d;
            font-size: 14px;
        }
        .schedule-date {
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
            color: #7f8c8d;
            font-size: 14px;
        }
        .today-classes {
            background-color: #e8f0fd;
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }
        .today-header {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
        }
        .today-header i {
            margin-right: 10px;
            color: #3498db;
            font-size: 24px;
        }
        .today-header h3 {
            margin: 0;
            color: #2c3e50;
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
                    <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="schedule.php" class="active"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>My Schedule</h1>
            </header>
            
            <div id="content-area" class="fade-in">
                <div class="content-box">
                    <div class="today-classes">
                        <div class="today-header">
                            <i class="fas fa-calendar-day"></i>
                            <h3>Today's Classes (<?php echo date('l, F j, Y'); ?>)</h3>
                        </div>
                        
                        <?php
                        $today_classes = false;
                        $schedules->data_seek(0); // Reset pointer
                        
                        while ($schedule = $schedules->fetch_assoc()) {
                            $days_array = explode(',', $schedule['days']);
                            
                            if (in_array($current_day, $days_array)) {
                                $today_classes = true;
                                ?>
                                <div class="schedule-card" style="margin-bottom: 10px;">
                                    <div class="schedule-body">
                                        <div style="display: flex; justify-content: space-between; align-items: center;">
                                            <h3 style="margin: 0; color: #2c3e50;"><?php echo htmlspecialchars($schedule['course_name']); ?></h3>
                                            <div style="background-color: #3498db; color: white; padding: 5px 10px; border-radius: 5px; font-size: 14px;">
                                                <?php echo date('h:i A', strtotime($schedule['start_time'])) . ' - ' . date('h:i A', strtotime($schedule['end_time'])); ?>
                                            </div>
                                        </div>
                                        <p style="margin-top: 10px; margin-bottom: 0; color: #7f8c8d;">
                                            <i class="fas fa-chalkboard-teacher"></i> 
                                            <?php echo htmlspecialchars($schedule['trainer_name']); ?>
                                        </p>
                                    </div>
                                </div>
                                <?php
                            }
                        }
                        
                        if (!$today_classes) {
                            echo '<p>You have no classes scheduled for today.</p>';
                        }
                        ?>
                    </div>
                    
                    <h2>Full Schedule</h2>
                    
                    <?php if ($schedules->num_rows === 0): ?>
                        <p>You have no scheduled classes for your enrolled courses.</p>
                    <?php else: ?>
                        <?php 
                        $schedules->data_seek(0); // Reset pointer
                        while ($schedule = $schedules->fetch_assoc()): 
                            $days_array = explode(',', $schedule['days']);
                        ?>
                            <div class="schedule-card">
                                <div class="schedule-header">
                                    <?php echo htmlspecialchars($schedule['course_name']); ?>
                                </div>
                                <div class="schedule-body">
                                    <div class="schedule-time">
                                        <div>
                                            <i class="far fa-clock"></i> 
                                            <?php echo date('h:i A', strtotime($schedule['start_time'])) . ' - ' . date('h:i A', strtotime($schedule['end_time'])); ?>
                                        </div>
                                    </div>
                                    
                                    <div class="schedule-days">
                                        <?php
                                        $all_days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        foreach ($all_days as $day) {
                                            $active_class = in_array($day, $days_array) ? 'active' : '';
                                            echo '<span class="day-badge ' . $active_class . '">' . $day . '</span>';
                                        }
                                        ?>
                                    </div>
                                    
                                    <div class="schedule-trainer">
                                        <i class="fas fa-chalkboard-teacher"></i> Trainer: 
                                        <?php echo htmlspecialchars($schedule['trainer_name']); ?>
                                    </div>
                                    
                                    <div class="schedule-date">
                                        <div><i class="far fa-calendar-alt"></i> From: <?php echo date('F j, Y', strtotime($schedule['start_date'])); ?></div>
                                        <div><i class="far fa-calendar-alt"></i> To: <?php echo date('F j, Y', strtotime($schedule['end_date'])); ?></div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php endif; ?>
                    
                    <a href="dashboard.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
