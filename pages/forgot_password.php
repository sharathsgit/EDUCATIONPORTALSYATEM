<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';
include '../includes/mailer.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $redirectUrl = "/{$_SESSION['role']}/dashboard.php";
    header("Location: $redirectUrl");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    
    if (empty($email)) {
        $error = "Please enter your email address.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    } else {
        // Try to find the email in students, admins, or trainers table
        $tables = ['students', 'admins', 'trainers'];
        $found = false;
        $user_role = '';
        
        foreach ($tables as $table) {
            $email_field = ($table === 'admins') ? 'email' : 'email_id';
            
            $stmt = $conn->prepare("SELECT * FROM $table WHERE $email_field = ?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows === 1) {
                $found = true;
                $user_role = substr($table, 0, -1); // Remove 's' from the end to get role
                break;
            }
        }
        
        if ($found) {
            // Generate a unique reset token
            $reset_token = generateToken();
            $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
            
            // Store token in password_resets table
            $stmt = $conn->prepare("INSERT INTO password_resets (email, token, role, expires_at) VALUES (?, ?, ?, ?)");
            
            // Create the table if it doesn't exist
            $conn->query("CREATE TABLE IF NOT EXISTS password_resets (
                id INT AUTO_INCREMENT PRIMARY KEY,
                email VARCHAR(100) NOT NULL,
                token VARCHAR(100) NOT NULL,
                role VARCHAR(20) NOT NULL,
                expires_at DATETIME NOT NULL,
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )");
            
            $stmt->bind_param("ssss", $email, $reset_token, $user_role, $expires);
            
            if ($stmt->execute() && sendPasswordResetEmail($email, $reset_token)) {
                $success = "A password reset link has been sent to your email address. The link will expire in 1 hour.";
            } else {
                $error = "Failed to send password reset email. Please try again.";
            }
        } else {
            // Don't reveal that the email doesn't exist for security reasons
            $success = "If the email is registered, a password reset link will be sent. Please check your inbox.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - Educational Management System</title>
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
                <h2>Forgot Password</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php else: ?>
                    <p>Enter your email address below to receive a password reset link.</p>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="email">Email Address</label>
                            <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email address" required>
                        </div>
                        
                        <button type="submit" class="form-btn">Request Password Reset</button>
                    </form>
                <?php endif; ?>
                
                <div class="form-footer">
                    <p>Remember your password? <a href="login.php">Back to Login</a></p>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 Educational Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
