<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as admin
requireRole('admin');

// Check if student ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header("Location: student.php");
    exit();
}

$student_id = $_GET['id'];
$success = "";
$error = "";

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header("Location: student.php");
    exit();
}

$student = $result->fetch_assoc();

// Handle student status update (verify/unverify)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['verify_student'])) {
        $stmt = $conn->prepare("UPDATE students SET flag = 1 WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        
        if ($stmt->execute()) {
            $success = "Student account has been verified.";
            // Update student data
            $student['flag'] = 1;
        } else {
            $error = "Failed to verify student.";
        }
    } elseif (isset($_POST['unverify_student'])) {
        $stmt = $conn->prepare("UPDATE students SET flag = 0 WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        
        if ($stmt->execute()) {
            $success = "Student account has been unverified.";
            // Update student data
            $student['flag'] = 0;
        } else {
            $error = "Failed to unverify student.";
        }
    }
}

// Fetch enrolled courses
$stmt = $conn->prepare("SELECT c.*, e.enrollment_date, e.status 
                      FROM enrollments e 
                      JOIN courses c ON e.course_id = c.id 
                      WHERE e.student_id = ? 
                      ORDER BY e.enrollment_date DESC");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$enrolled_courses = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Student - Educational Management System</title>
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        .profile-section {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .profile-header {
            display: flex;
            align-items: center;
            margin-bottom: 20px;
        }
        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            margin-right: 20px;
            object-fit: cover;
            background-color: #3498db;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 36px;
            font-weight: bold;
        }
        .profile-name h2 {
            margin: 0;
            color: #2c3e50;
        }
        .profile-name p {
            margin: 5px 0 0;
            color: #7f8c8d;
        }
        .status-badge {
            display: inline-block;
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        .status-verified {
            background-color: #d4f5e2;
            color: #27ae60;
        }
        .status-unverified {
            background-color: #fdebd0;
            color: #e67e22;
        }
        .profile-info {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .info-group {
            margin-bottom: 15px;
        }
        .info-label {
            font-weight: bold;
            color: #7f8c8d;
            font-size: 14px;
            margin-bottom: 5px;
        }
        .info-value {
            color: #2c3e50;
        }
        .course-card {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 15px;
            margin-bottom: 15px;
        }
        .course-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }
        .course-name {
            font-weight: bold;
            color: #2c3e50;
        }
        .course-status {
            font-size: 12px;
            padding: 3px 8px;
            border-radius: 20px;
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
        .action-buttons {
            display: flex;
            gap: 10px;
            margin-top: 20px;
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
                    <li><a href="student.php" class="active"><i class="fas fa-user-graduate"></i> Student Management</a></li>
                    <li><a href="course_management.php"><i class="fas fa-book"></i> Course Management</a></li>
                    <li><a href="schedule_management.php"><i class="fas fa-calendar-alt"></i> Schedule Management</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Student Profile: <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h1>
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
                    
                    <div class="profile-section">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <?php echo strtoupper(substr($student['first_name'], 0, 1) . substr($student['last_name'], 0, 1)); ?>
                            </div>
                            <div class="profile-name">
                                <h2>
                                    <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                    <span class="status-badge <?php echo $student['flag'] == 1 ? 'status-verified' : 'status-unverified'; ?>">
                                        <?php echo $student['flag'] == 1 ? 'Verified' : 'Not Verified'; ?>
                                    </span>
                                </h2>
                                <p>Student ID: <?php echo htmlspecialchars($student['student_id']); ?></p>
                                <p>Joined on <?php echo date('F j, Y', strtotime($student['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="action-buttons">
                            <?php if ($student['flag'] == 0): ?>
                                <form method="POST">
                                    <button type="submit" name="verify_student" class="btn btn-success">
                                        <i class="fas fa-check"></i> Verify Student
                                    </button>
                                </form>
                            <?php else: ?>
                                <form method="POST">
                                    <button type="submit" name="unverify_student" class="btn btn-warning">
                                        <i class="fas fa-times"></i> Unverify Student
                                    </button>
                                </form>
                            <?php endif; ?>
                        </div>
                        
                        <div class="profile-info">
                            <div>
                                <div class="info-group">
                                    <div class="info-label">Email Address</div>
                                    <div class="info-value"><?php echo htmlspecialchars($student['email_id']); ?></div>
                                </div>
                                
                                <div class="info-group">
                                    <div class="info-label">Phone Number</div>
                                    <div class="info-value"><?php echo htmlspecialchars($student['phone_no']); ?></div>
                                </div>
                                
                                <div class="info-group">
                                    <div class="info-label">Login ID</div>
                                    <div class="info-value"><?php echo htmlspecialchars($student['loginid']); ?></div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="info-group">
                                    <div class="info-label">Address</div>
                                    <div class="info-value"><?php echo htmlspecialchars($student['address'] ?? 'Not provided'); ?></div>
                                </div>
                                
                                <div class="info-group">
                                    <div class="info-label">City, State</div>
                                    <div class="info-value">
                                        <?php 
                                        $location = [];
                                        if (!empty($student['city'])) $location[] = $student['city'];
                                        if (!empty($student['district'])) $location[] = $student['district'];
                                        if (!empty($student['state'])) $location[] = $student['state'];
                                        echo !empty($location) ? htmlspecialchars(implode(', ', $location)) : 'Not provided';
                                        ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div>
                                <div class="info-group">
                                    <div class="info-label">Alternate Email</div>
                                    <div class="info-value">
                                        <?php echo !empty($student['alternate_email_id']) ? htmlspecialchars($student['alternate_email_id']) : 'Not provided'; ?>
                                    </div>
                                </div>
                                
                                <div class="info-group">
                                    <div class="info-label">Alternate Phone</div>
                                    <div class="info-value">
                                        <?php echo !empty($student['alternate_phone_no']) ? htmlspecialchars($student['alternate_phone_no']) : 'Not provided'; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="profile-section">
                        <h3>Enrolled Courses</h3>
                        
                        <?php if ($enrolled_courses->num_rows === 0): ?>
                            <p>This student is not enrolled in any courses yet.</p>
                        <?php else: ?>
                            <?php while ($course = $enrolled_courses->fetch_assoc()): ?>
                                <div class="course-card">
                                    <div class="course-header">
                                        <div class="course-name"><?php echo htmlspecialchars($course['course_name']); ?></div>
                                        <div class="course-status status-<?php echo $course['status']; ?>">
                                            <?php echo ucfirst($course['status']); ?>
                                        </div>
                                    </div>
                                    <div>
                                        <p><?php echo substr(htmlspecialchars($course['description']), 0, 150) . (strlen($course['description']) > 150 ? '...' : ''); ?></p>
                                        <div style="display: flex; justify-content: space-between; margin-top: 10px; font-size: 14px; color: #7f8c8d;">
                                            <div>Enrolled on: <?php echo date('M d, Y', strtotime($course['enrollment_date'])); ?></div>
                                            <div>Price: $<?php echo number_format($course['price'], 2); ?></div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </div>
                    
                    <a href="student.php" class="back-btn">
                        <i class="fas fa-arrow-left"></i> Back to Student List
                    </a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
