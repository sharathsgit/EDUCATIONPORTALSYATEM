<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $redirectUrl = "/{$_SESSION['role']}/dashboard.php";
    header("Location: $redirectUrl");
    exit();
}

$error = "";
$success = "";

// Ensure the table exists
$conn->query("CREATE TABLE IF NOT EXISTS password_resets (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100) NOT NULL,
    token VARCHAR(100) NOT NULL,
    role VARCHAR(20) NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
)");

// Check if token is provided
if (!isset($_GET['token']) || empty($_GET['token'])) {
    header("Location: login.php");
    exit();
}

$token = $_GET['token'];

// Verify token and check if it's expired
$stmt = $conn->prepare("SELECT * FROM password_resets WHERE token = ? AND expires_at > NOW() ORDER BY created_at DESC LIMIT 1");
$stmt->bind_param("s", $token);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    $error = "Invalid or expired password reset link. Please request a new one.";
} else {
    $reset_info = $result->fetch_assoc();
    $email = $reset_info['email'];
    $role = $reset_info['role'];
    
    // Process password reset
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (strlen($password) < 6) {
            $error = "Password must be at least 6 characters long.";
        } elseif ($password !== $confirm_password) {
            $error = "Passwords do not match.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $updated = false;
            
            // Update password based on role
            switch ($role) {
                case 'student':
                    $stmt = $conn->prepare("UPDATE students SET password = ? WHERE email_id = ?");
                    $stmt->bind_param("ss", $hashed_password, $email);
                    $updated = $stmt->execute();
                    break;
                    
                case 'admin':
                    $stmt = $conn->prepare("UPDATE admins SET password = ? WHERE email = ?");
                    $stmt->bind_param("ss", $hashed_password, $email);
                    $updated = $stmt->execute();
                    break;
                    
                case 'trainer':
                    $stmt = $conn->prepare("UPDATE trainers SET password = ? WHERE email_id = ?");
                    $stmt->bind_param("ss", $hashed_password, $email);
                    $updated = $stmt->execute();
                    break;
            }
            
            if ($updated) {
                // Delete all reset tokens for this email
                $stmt = $conn->prepare("DELETE FROM password_resets WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                
                $success = "Your password has been reset successfully. You can now login with your new password.";
            } else {
                $error = "Failed to update password. Please try again.";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - Educational Management System</title>
    <link rel="stylesheet" href="../css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Educational Management System</h1>
        </div>
        
        <div class="container">
            <div class="form-container">
                <h2>Reset Password</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                        <p><a href="login.php">Click here to login</a></p>
                    </div>
                <?php elseif (empty($error) || $error !== "Invalid or expired password reset link. Please request a new one."): ?>
                    <p>Enter your new password below to reset your account.</p>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="password">New Password</label>
                            <input type="password" name="password" id="password" class="form-control" placeholder="Enter new password" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="confirm_password">Confirm New Password</label>
                            <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm new password" required>
                        </div>
                        
                        <button type="submit" class="form-btn">Reset Password</button>
                    </form>
                <?php else: ?>
                    <div class="form-footer">
                        <p><a href="forgot_password.php">Request a new password reset link</a></p>
                    </div>
                <?php endif; ?>
                
                <div class="form-footer">
                    <p><a href="login.php">Back to Login</a></p>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 Educational Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
