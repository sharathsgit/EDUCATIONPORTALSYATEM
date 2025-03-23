<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Check if user is logged in as trainer
requireRole('trainer');

$trainer_id = $_SESSION['user_id'];
$success = "";
$error = "";

// Fetch trainer data
$stmt = $conn->prepare("SELECT * FROM trainers WHERE trainer_id = ?");
$stmt->bind_param("s", $trainer_id);
$stmt->execute();
$result = $stmt->get_result();
$trainer = $result->fetch_assoc();

// Handle profile update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $phone_no = cleanInput($_POST['phone_no']);
    $expertise = cleanInput($_POST['expertise']);
    
    $stmt = $conn->prepare("UPDATE trainers SET phone_no=?, expertise=? WHERE trainer_id=?");
    $stmt->bind_param("sss", $phone_no, $expertise, $trainer_id);
    
    if ($stmt->execute()) {
        $success = "Profile updated successfully!";
        
        // Refresh trainer data
        $stmt = $conn->prepare("SELECT * FROM trainers WHERE trainer_id = ?");
        $stmt->bind_param("s", $trainer_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $trainer = $result->fetch_assoc();
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
    if (!password_verify($current_password, $trainer['password'])) {
        $error = "Current password is incorrect.";
    } elseif ($new_password !== $confirm_password) {
        $error = "New passwords do not match.";
    } elseif (strlen($new_password) < 6) {
        $error = "New password must be at least 6 characters long.";
    } else {
        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE trainers SET password=? WHERE trainer_id=?");
        $stmt->bind_param("ss", $hashed_password, $trainer_id);
        
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
    <title>Trainer Profile - Educational Management System</title>
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
                    <li><a href="dashboard.php"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="profile.php" class="active"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="my_courses.php"><i class="fas fa-book"></i> My Courses</a></li>
                    <li><a href="my_schedule.php"><i class="fas fa-calendar-alt"></i> My Schedule</a></li>
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
                                <p><strong>Trainer ID:</strong> <?php echo htmlspecialchars($trainer['trainer_id']); ?></p>
                                <p><strong>Name:</strong> <?php echo htmlspecialchars($trainer['first_name'] . ' ' . $trainer['last_name']); ?></p>
                                <p><strong>Email:</strong> <?php echo htmlspecialchars($trainer['email_id']); ?></p>
                                <p><strong>Login ID:</strong> <?php echo htmlspecialchars($trainer['loginid']); ?></p>
                                <p><strong>Expertise:</strong> <?php echo htmlspecialchars($trainer['expertise']); ?></p>
                                <p><strong>Joined:</strong> <?php echo date('F j, Y', strtotime($trainer['created_at'])); ?></p>
                            </div>
                        </div>
                        
                        <div class="form-section" style="flex: 2; min-width: 300px;">
                            <h3>Update Profile</h3>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="phone_no">Phone Number</label>
                                    <input type="text" name="phone_no" id="phone_no" class="form-control" value="<?php echo htmlspecialchars($trainer['phone_no']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="expertise">Areas of Expertise</label>
                                    <textarea name="expertise" id="expertise" class="form-control" rows="4" required><?php echo htmlspecialchars($trainer['expertise']); ?></textarea>
                                    <small style="color: #7f8c8d;">List your areas of expertise, separated by commas (e.g., "Java Programming, Web Development, Data Science")</small>
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
