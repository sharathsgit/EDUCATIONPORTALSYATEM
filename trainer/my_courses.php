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

// Fetch assigned courses
$stmt = $conn->prepare("SELECT c.*, 
                      (SELECT COUNT(*) FROM enrollments WHERE course_id = c.id) AS enrollment_count 
                      FROM courses c 
                      WHERE c.trainer_id = ? 
                      ORDER BY c.created_at DESC");
$stmt->bind_param("s", $trainer_id);
$stmt->execute();
$courses = $stmt->get_result();
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
        }
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }
        .course-title {
            font-size: 20px;
            font-weight: bold;
            color: #2c3e50;
        }
        .course-price {
            font-weight: bold;
            color: #27ae60;
        }
        .course-description {
            color: #7f8c8d;
            margin-bottom: 20px;
        }
        .course-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        .enrollment-count {
            display: flex;
            align-items: center;
            color: #3498db;
            font-weight: bold;
        }
        .enrollment-count i {
            margin-right: 5px;
        }
        .course-date {
            color: #95a5a6;
            font-size: 14px;
        }
        .student-list {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-top: 15px;
        }
        .student-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 14px;
        }
        .student-table th, .student-table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #e0e0e0;
        }
        .student-table th {
            color: #2c3e50;
            font-weight: bold;
        }
        .toggle-btn {
            background-color: transparent;
            border: none;
            color: #3498db;
            cursor: pointer;
            font-size: 14px;
            display: flex;
            align-items: center;
        }
        .toggle-btn i {
            margin-right: 5px;
        }
        .badge {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 50px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-success {
            background-color: #d4f5e2;
            color: #27ae60;
        }
        .badge-warning {
            background-color: #fdebd0;
            color: #f39c12;
        }
        .badge-danger {
            background-color: #f5b7b1;
            color: #c0392b;
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
                    <li><a href="my_courses.php" class="active"><i class="fas fa-book"></i> My Courses</a></li>
                    <li><a href="my_schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
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
                    <h2>Courses Assigned to Me</h2>
                    
                    <?php if ($courses->num_rows === 0): ?>
                        <p>You have no courses assigned to you yet.</p>
                    <?php else: ?>
                        <?php while ($course = $courses->fetch_assoc()): ?>
                            <div class="course-card">
                                <div class="course-header">
                                    <div class="course-title"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                    <div class="course-price">$<?php echo number_format($course['price'], 2); ?></div>
                                </div>
                                
                                <div class="course-description">
                                    <?php echo nl2br(htmlspecialchars($course['description'])); ?>
                                </div>
                                
                                <div class="course-meta">
                                    <div class="enrollment-count">
                                        <i class="fas fa-user-graduate"></i> <?php echo $course['enrollment_count']; ?> students enrolled
                                    </div>
                                    <div class="course-date">
                                        <i class="far fa-calendar-alt"></i> Added on <?php echo date('M d, Y', strtotime($course['created_at'])); ?>
                                    </div>
                                </div>
                                
                                <?php if ($course['enrollment_count'] > 0): ?>
                                    <button class="toggle-btn" onclick="toggleStudentList('student-list-<?php echo $course['id']; ?>')">
                                        <i class="fas fa-chevron-down"></i> Show Enrolled Students
                                    </button>
                                    
                                    <div id="student-list-<?php echo $course['id']; ?>" class="student-list" style="display: none;">
                                        <?php
                                        // Fetch enrolled students for this course
                                        $stmt = $conn->prepare("SELECT s.student_id, s.first_name, s.last_name, s.email_id, 
                                                              e.enrollment_date, e.status 
                                                              FROM enrollments e 
                                                              JOIN students s ON e.student_id = s.student_id 
                                                              WHERE e.course_id = ? 
                                                              ORDER BY e.enrollment_date DESC");
                                        $stmt->bind_param("i", $course['id']);
                                        $stmt->execute();
                                        $enrolled_students = $stmt->get_result();
                                        ?>
                                        
                                        <table class="student-table">
                                            <thead>
                                                <tr>
                                                    <th>Name</th>
                                                    <th>Email</th>
                                                    <th>Status</th>
                                                    <th>Enrolled On</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php while ($student = $enrolled_students->fetch_assoc()): ?>
                                                    <tr>
                                                        <td><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></td>
                                                        <td><?php echo htmlspecialchars($student['email_id']); ?></td>
                                                        <td>
                                                            <?php 
                                                            $status_class = '';
                                                            $status_text = ucfirst($student['status']);
                                                            
                                                            switch ($student['status']) {
                                                                case 'active':
                                                                    $status_class = 'badge-success';
                                                                    break;
                                                                case 'completed':
                                                                    $status_class = 'badge-warning';
                                                                    break;
                                                                case 'dropped':
                                                                    $status_class = 'badge-danger';
                                                                    break;
                                                            }
                                                            ?>
                                                            <span class="badge <?php echo $status_class; ?>"><?php echo $status_text; ?></span>
                                                        </td>
                                                        <td><?php echo date('M d, Y', strtotime($student['enrollment_date'])); ?></td>
                                                    </tr>
                                                <?php endwhile; ?>
                                            </tbody>
                                        </table>
                                    </div>
                                <?php endif; ?>
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
    
    <script>
        function toggleStudentList(elementId) {
            const element = document.getElementById(elementId);
            const button = element.previousElementSibling;
            
            if (element.style.display === 'none') {
                element.style.display = 'block';
                button.innerHTML = '<i class="fas fa-chevron-up"></i> Hide Enrolled Students';
            } else {
                element.style.display = 'none';
                button.innerHTML = '<i class="fas fa-chevron-down"></i> Show Enrolled Students';
            }
        }
    </script>
</body>
</html>
