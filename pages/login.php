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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $error = "Please fill in all fields.";
    } else {
        $user = authenticateUser($conn, $email, $password);
        
        if ($user) {
            // Set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['name'] = $user['name'];
            $_SESSION['role'] = $user['role'];
            
            // Redirect based on role
            $redirectUrl = "/{$user['role']}/dashboard.php";
            header("Location: $redirectUrl");
            exit();
        } else {
            $error = "Invalid login credentials or account not verified.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Educational Management System</title>
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
                <h2>Login</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="email">Email / Username</label>
                        <input type="text" name="email" id="email" class="form-control" placeholder="Enter your email or username" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Enter your password" required>
                    </div>
                    
                    <button type="submit" class="form-btn">Login</button>
                </form>
                
                <div class="form-footer">
                    <p>Don't have an account? <a href="register.php">Register as a Student</a></p>
                    <p><a href="forgot_password.php">Forgot Password?</a></p>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 Educational Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
