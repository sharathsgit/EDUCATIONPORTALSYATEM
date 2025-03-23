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

// Get current day of week
$current_day = date('l');
$current_date = date('Y-m-d');

// Fetch schedules
$stmt = $conn->prepare("SELECT s.*, c.course_name 
                      FROM schedules s 
                      JOIN courses c ON s.course_id = c.id 
                      WHERE s.trainer_id = ? 
                      ORDER BY s.start_date, s.start_time");
$stmt->bind_param("s", $trainer_id);
$stmt->execute();
$schedules = $stmt->get_result();
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
            display: flex;
            justify-content: space-between;
            align-items: center;
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
        .week-view {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 10px;
            margin-top: 30px;
            margin-bottom: 30px;
        }
        .day-column {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            min-height: 300px;
        }
        .day-column h4 {
            text-align: center;
            margin-top: 0;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
            color: #2c3e50;
        }
        .day-column.today {
            background-color: #e8f0fd;
            box-shadow: 0 0 10px rgba(52, 152, 219, 0.3);
        }
        .class-item {
            background-color: white;
            border-radius: 5px;
            padding: 10px;
            margin-bottom: 10px;
            border-left: 3px solid #3498db;
            font-size: 14px;
        }
        .class-item p {
            margin: 5px 0;
        }
        .class-time {
            font-weight: bold;
            color: #2c3e50;
        }
        .class-course {
            color: #3498db;
        }
        @media (max-width: 768px) {
            .week-view {
                grid-template-columns: 1fr;
            }
        }
    </style>
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
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                    <li><a href="my_schedule.php" class="active"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
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
                            
                            if (in_array($current_day, $days_array) && $schedule['start_date'] <= $current_date && $schedule['end_date'] >= $current_date) {
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
                    
                    <h2>Weekly Schedule</h2>
                    
                    <?php if ($schedules->num_rows === 0): ?>
                        <p>You have no classes scheduled.</p>
                    <?php else: ?>
                        <div class="week-view">
                            <?php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            
                            foreach ($days as $day) {
                                $is_today = ($day === $current_day);
                                ?>
                                <div class="day-column <?php echo $is_today ? 'today' : ''; ?>">
                                    <h4><?php echo $day; ?></h4>
                                    
                                    <?php
                                    $day_has_classes = false;
                                    $schedules->data_seek(0); // Reset pointer
                                    
                                    while ($schedule = $schedules->fetch_assoc()) {
                                        $days_array = explode(',', $schedule['days']);
                                        
                                        if (in_array($day, $days_array)) {
                                            $day_has_classes = true;
                                            ?>
                                            <div class="class-item">
                                                <p class="class-course"><?php echo htmlspecialchars($schedule['course_name']); ?></p>
                                                <p class="class-time">
                                                    <?php echo date('h:i A', strtotime($schedule['start_time'])) . ' - ' . date('h:i A', strtotime($schedule['end_time'])); ?>
                                                </p>
                                            </div>
                                            <?php
                                        }
                                    }
                                    
                                    if (!$day_has_classes) {
                                        echo '<p style="color: #95a5a6; text-align: center; margin-top: 30px;">No classes</p>';
                                    }
                                    ?>
                                </div>
                                <?php
                            }
                            ?>
                        </div>
                        
                        <h2>Full Schedule Details</h2>
                        
                        <?php 
                        $schedules->data_seek(0); // Reset pointer
                        while ($schedule = $schedules->fetch_assoc()): 
                            $days_array = explode(',', $schedule['days']);
                        ?>
                            <div class="schedule-card">
                                <div class="schedule-header">
                                    <div><?php echo htmlspecialchars($schedule['course_name']); ?></div>
                                    <div>
                                        <?php 
                                        $status = '';
                                        if ($schedule['end_date'] < $current_date) {
                                            $status = 'Completed';
                                        } elseif ($schedule['start_date'] > $current_date) {
                                            $status = 'Upcoming';
                                        } else {
                                            $status = 'Active';
                                        }
                                        echo $status;
                                        ?>
                                    </div>
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
