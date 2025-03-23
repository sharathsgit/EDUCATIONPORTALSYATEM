<?php
session_start();
include '../includes/db.php';
include '../includes/auth.php';

// Redirect if verification info is not set
if (!isset($_SESSION['verification_email']) || !isset($_SESSION['verification_code'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['verification_email'];
$stored_code = $_SESSION['verification_code'];
$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $submitted_code = cleanInput($_POST['verification_code']);
    
    if (empty($submitted_code)) {
        $error = "Please enter the verification code.";
    } elseif ($submitted_code == $stored_code) {
        // Update student status to verified
        $stmt = $conn->prepare("UPDATE students SET flag = 1 WHERE email_id = ?");
        $stmt->bind_param("s", $email);
        
        if ($stmt->execute()) {
            // Clear verification session data
            unset($_SESSION['verification_email']);
            unset($_SESSION['verification_code']);
            
            $success = "Your account has been verified successfully! You can now login.";
        } else {
            $error = "Failed to verify account. Please try again.";
        }
    } else {
        $error = "Invalid verification code. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Account - Educational Management System</title>
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
                <h2>Verify Your Account</h2>
                
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
                <?php else: ?>
                    <p>A verification code has been sent to your email address. Please enter the code below:</p>
                    
                    <form method="POST" action="">
                        <div class="form-group">
                            <label for="verification_code">Verification Code</label>
                            <input type="text" name="verification_code" id="verification_code" class="form-control" placeholder="Enter verification code" required>
                        </div>
                        
                        <button type="submit" class="form-btn">Verify Account</button>
                    </form>
                    
                    <div class="form-footer">
                        <p>Didn't receive the code? <a href="#" id="resend-code">Resend Code</a></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 Educational Management System. All rights reserved.</p>
        </footer>
    </div>
    
    <script>
        document.getElementById('resend-code').addEventListener('click', function(e) {
            e.preventDefault();
            // Create a form to submit for code resending
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'resend_code.php';
            document.body.appendChild(form);
            form.submit();
        });
    </script>
</body>
</html>
