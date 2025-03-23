<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as admin
requireRole('admin');

// Handle student verification/unverification
if (isset($_POST['verify_student'])) {
    $student_id = $_POST['student_id'];
    $stmt = $conn->prepare("UPDATE students SET flag = 1 WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
}

if (isset($_POST['unverify_student'])) {
    $student_id = $_POST['student_id'];
    $stmt = $conn->prepare("UPDATE students SET flag = 0 WHERE student_id = ?");
    $stmt->bind_param("s", $student_id);
    $stmt->execute();
}

// Fetch all students
$result = $conn->query("SELECT * FROM students ORDER BY created_at DESC");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - Educational Management System</title>
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
                <h1>Student Management</h1>
            </header>
            
            <div id="content-area" class="fade-in">
                <div class="content-box">
                    <h2>Student List</h2>
                    
                    <div class="table-container" style="overflow-x: auto;">
                        <table class="styled-table">
                            <thead>
                                <tr>
                                    <th>Student ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Phone</th>
                                    <th>City</th>
                                    <th>Registration Date</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($student = $result->fetch_assoc()): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($student['student_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['first_name'] . " " . $student['last_name']); ?></td>
                                        <td><?php echo htmlspecialchars($student['email_id']); ?></td>
                                        <td><?php echo htmlspecialchars($student['phone_no']); ?></td>
                                        <td><?php echo htmlspecialchars($student['city'] ?? '—'); ?></td>
                                        <td><?php echo date('M d, Y', strtotime($student['created_at'])); ?></td>
                                        <td>
                                            <?php if ($student['flag'] == 1): ?>
                                                <span style="color: green; font-weight: bold;">Verified</span>
                                            <?php else: ?>
                                                <span style="color: red; font-weight: bold;">Not Verified</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if ($student['flag'] == 0): ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                    <button type="submit" name="verify_student" class="btn btn-success" style="padding: 5px 10px; font-size: 12px;">
                                                        <i class="fas fa-check"></i> Verify
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <form method="POST" style="display: inline;">
                                                    <input type="hidden" name="student_id" value="<?php echo $student['student_id']; ?>">
                                                    <button type="submit" name="unverify_student" class="btn btn-warning" style="padding: 5px 10px; font-size: 12px;">
                                                        <i class="fas fa-times"></i> Unverify
                                                    </button>
                                                </form>
                                            <?php endif; ?>
                                            
                                            <a href="view_student.php?id=<?php echo $student['student_id']; ?>" class="btn" style="padding: 5px 10px; font-size: 12px;">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                                
                                <?php if ($result->num_rows === 0): ?>
                                    <tr>
                                        <td colspan="8" style="text-align: center;">No students found.</td>
                                    </tr>
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
