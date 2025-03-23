<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as student
requireRole('student');

$student_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch student data
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone_no = cleanInput($_POST['phone_no']);
    $address = cleanInput($_POST['address']);
    $state = cleanInput($_POST['state']);
    $district = cleanInput($_POST['district']);
    $city = cleanInput($_POST['city']);
    $alternate_email_id = !empty($_POST['alternate_email_id']) ? cleanInput($_POST['alternate_email_id']) : null;
    $alternate_phone_no = !empty($_POST['alternate_phone_no']) ? cleanInput($_POST['alternate_phone_no']) : null;
    
    $stmt = $conn->prepare("UPDATE students SET phone_no=?, address=?, state=?, district=?, city=?, alternate_email_id=?, alternate_phone_no=? WHERE student_id=?");
    $stmt->bind_param("ssssssss", $phone_no, $address, $state, $district, $city, $alternate_email_id, $alternate_phone_no, $student_id);
    
    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        
        // Refresh student data
        $stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
        $stmt->bind_param("s", $student_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $student = $result->fetch_assoc();
    } else {
        $error = "Error updating profile: " . $conn->error;
    }
}

// Handle password change
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    // Verify current password
    if (!password_verify($current_password, $student['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE students SET password=? WHERE student_id=?");
        $stmt->bind_param("ss", $hashed_password, $student_id);
        
        if ($stmt->execute()) {
            $success = "Password changed successfully!";
        } else {
            $error = "Error changing password: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile - Educational Management System</title>
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
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="schedule.php"><i class="fas fa-calendar-alt"></i> Schedule</a></li>
                    <li><a href="../pages/logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>My Profile</h1>
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
                    
                    <div class="profile-container" style="display: flex; flex-wrap: wrap; gap: 20px;">
                        <div class="profile-section" style="flex: 1; min-width: 300px; background: #f8f9fa; padding: 20px; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                            <h3 style="margin-bottom: 20px; border-bottom: 2px solid #3498db; padding-bottom: 10px;">Personal Information</h3>
                            
                            <div style="margin-bottom: 15px;">
                                <p><strong>Student ID:</strong> <?php echo htmlspecialchars($student['student_id']); ?></p>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($student['email_id']); ?></p>
                                <p><strong>Login ID:</strong> <?php echo htmlspecialchars($student['loginid']); ?></p>
                                <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($student['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="form-section" style="flex: 2; min-width: 300px;">
                            <h3>Update Profile</h3>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="phone_no">Phone Number</label>
                                    <input type="text" name="phone_no" id="phone_no" class="form-control" value="<?php echo htmlspecialchars($student['phone_no']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="address">Address</label>
                                    <input type="text" name="address" id="address" class="form-control" value="<?php echo htmlspecialchars($student['address'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="state">State</label>
                                    <input type="text" name="state" id="state" class="form-control" value="<?php echo htmlspecialchars($student['state'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="district">District</label>
                                    <input type="text" name="district" id="district" class="form-control" value="<?php echo htmlspecialchars($student['district'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="city">City</label>
                                    <input type="text" name="city" id="city" class="form-control" value="<?php echo htmlspecialchars($student['city'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="alternate_email_id">Alternate Email</label>
                                    <input type="email" name="alternate_email_id" id="alternate_email_id" class="form-control" value="<?php echo htmlspecialchars($student['alternate_email_id'] ?? ''); ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="alternate_phone_no">Alternate Phone</label>
                                    <input type="text" name="alternate_phone_no" id="alternate_phone_no" class="form-control" value="<?php echo htmlspecialchars($student['alternate_phone_no'] ?? ''); ?>">
                                </div>
                                
                                <button type="submit" name="update_profile" class="btn">Update Profile</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="form-section" style="margin-top: 30px;">
                        <h3>Change Password</h3>
                        <form method="POST" action="">
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" name="current_password" id="current_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" name="new_password" id="new_password" class="form-control" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" name="confirm_password" id="confirm_password" class="form-control" required>
                            </div>
                            
                            <button type="submit" name="change_password" class="btn btn-warning">Change Password</button>
                        </form>
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
