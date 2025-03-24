<!-- username --admin@example.com
 password -- admin123 -->

<?php
session_start();

// Redirect to appropriate dashboard if already logged in
if (isset($_SESSION['role'])) {
    switch ($_SESSION['role']) {
        case 'student':
            header("Location: student/dashboard.php");
            exit();
        case 'admin':
            header("Location: admin/dashboard.php");
            exit();
        case 'trainer':
            header("Location: trainer/dashboard.php");
            exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Educational Management System</title>
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <div class="main-container">
        <div class="header">
            <h1><i class="fas fa-graduation-cap"></i> Educational Management System</h1>
        </div>
        
        <div class="container">
            <div class="welcome-section">
                <h2>Welcome to the Educational Management System</h2>
                <p>Access your account by logging in or create a new student account</p>
                
                <div class="card-container">
                    <div class="info-card">
                        <i class="fas fa-user-graduate"></i>
                        <h3>Students</h3>
                        <p>Access your courses, schedules and track your progress</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-chalkboard-teacher"></i>
                        <h3>Trainers</h3>
                        <p>Manage your courses and track student progress</p>
                    </div>
                    <div class="info-card">
                        <i class="fas fa-user-cog"></i>
                        <h3>Administrators</h3>
                        <p>Manage students, courses, and overall system</p>
                    </div>
                </div>

                <div class="action-buttons">
                    <a href="pages/login.php" class="btn primary-btn"><i class="fas fa-sign-in-alt"></i> Login</a>
                    <a href="pages/register.php" class="btn secondary-btn"><i class="fas fa-user-plus"></i> Register as Student</a>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 Educational Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
