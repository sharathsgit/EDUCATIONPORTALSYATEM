<?php
session_start();
include '../includes/db.php';
include '../includes/mailer.php';
include '../includes/auth.php';

// Redirect if already logged in
if (isLoggedIn()) {
    $redirectUrl = "/{$_SESSION['role']}/dashboard.php";
    header("Location: $redirectUrl");
    exit();
}

$error = "";
$success = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Clean and validate all inputs
    $first_name = cleanInput($_POST['first_name']);
    $last_name = cleanInput($_POST['last_name']);
    $email = cleanInput($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = cleanInput($_POST['phone']);
    $address = cleanInput($_POST['address']);
    $state = cleanInput($_POST['state']);
    $district = cleanInput($_POST['district']);
    $city = cleanInput($_POST['city']);
    $alt_email = !empty($_POST['alt_email']) ? cleanInput($_POST['alt_email']) : null;
    $alt_phone = !empty($_POST['alt_phone']) ? cleanInput($_POST['alt_phone']) : null;
    
    // Validate required fields
    if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($phone)) {
        $error = "Please fill in all required fields.";
    }
    // Validate email format
    elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address.";
    }
    // Validate password match
    elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    }
    // Check if email already exists
    else {
        $stmt = $conn->prepare("SELECT * FROM students WHERE email_id = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $error = "Email address is already registered.";
        } else {
            // Generate student ID, login ID, and verification code
            $student_id = "STU" . rand(100000, 999999);
            $loginid = strtolower($first_name . rand(10, 99));
            $verification_code = rand(100000, 999999);
            
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            
            // Save verification code in session for verification page
            $_SESSION['verification_email'] = $email;
            $_SESSION['verification_code'] = $verification_code;
            
            // Insert student data
            $stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, loginid, password, phone_no, address, state, district, city, email_id, alternate_email_id, alternate_phone_no, flag) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
            $stmt->bind_param("sssssssssssss", $student_id, $first_name, $last_name, $loginid, $hashed_password, $phone, $address, $state, $district, $city, $email, $alt_email, $alt_phone);
            
            if ($stmt->execute()) {
                // Send verification email
                if (sendVerificationEmail($email, $verification_code)) {
                    // Redirect to verification page
                    header("Location: verify.php");
                    exit();
                } else {
                    $error = "Failed to send verification email. Please try again.";
                }
            } else {
                $error = "Error registering account: " . $conn->error;
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
    <title>Student Registration - Educational Management System</title>
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
                <h2>Student Registration</h2>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="first_name">First Name*</label>
                        <input type="text" name="first_name" id="first_name" class="form-control" placeholder="Enter your first name" required value="<?php echo isset($_POST['first_name']) ? htmlspecialchars($_POST['first_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Last Name*</label>
                        <input type="text" name="last_name" id="last_name" class="form-control" placeholder="Enter your last name" required value="<?php echo isset($_POST['last_name']) ? htmlspecialchars($_POST['last_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address*</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="Enter your email" required value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="password">Password*</label>
                        <input type="password" name="password" id="password" class="form-control" placeholder="Create a password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="confirm_password">Confirm Password*</label>
                        <input type="password" name="confirm_password" id="confirm_password" class="form-control" placeholder="Confirm your password" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="phone">Phone Number*</label>
                        <input type="text" name="phone" id="phone" class="form-control" placeholder="Enter your phone number" required value="<?php echo isset($_POST['phone']) ? htmlspecialchars($_POST['phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="address">Address</label>
                        <input type="text" name="address" id="address" class="form-control" placeholder="Enter your address" value="<?php echo isset($_POST['address']) ? htmlspecialchars($_POST['address']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="state">State</label>
                        <input type="text" name="state" id="state" class="form-control" placeholder="Enter your state" value="<?php echo isset($_POST['state']) ? htmlspecialchars($_POST['state']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="district">District</label>
                        <input type="text" name="district" id="district" class="form-control" placeholder="Enter your district" value="<?php echo isset($_POST['district']) ? htmlspecialchars($_POST['district']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="city">City</label>
                        <input type="text" name="city" id="city" class="form-control" placeholder="Enter your city" value="<?php echo isset($_POST['city']) ? htmlspecialchars($_POST['city']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="alt_email">Alternate Email</label>
                        <input type="email" name="alt_email" id="alt_email" class="form-control" placeholder="Enter alternate email (optional)" value="<?php echo isset($_POST['alt_email']) ? htmlspecialchars($_POST['alt_email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="alt_phone">Alternate Phone</label>
                        <input type="text" name="alt_phone" id="alt_phone" class="form-control" placeholder="Enter alternate phone (optional)" value="<?php echo isset($_POST['alt_phone']) ? htmlspecialchars($_POST['alt_phone']) : ''; ?>">
                    </div>
                    
                    <button type="submit" class="form-btn">Register</button>
                </form>
                
                <div class="form-footer">
                    <p>Already have an account? <a href="login.php">Login</a></p>
                </div>
            </div>
        </div>
        
        <footer>
            <p>&copy; 2023 Educational Management System. All rights reserved.</p>
        </footer>
    </div>
</body>
</html>
