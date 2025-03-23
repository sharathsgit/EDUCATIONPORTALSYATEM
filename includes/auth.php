<?php
/**
 * Authentication helper functions
 */

// Check if user is logged in
function isLoggedIn() {
    return isset($_SESSION['user_id']) && isset($_SESSION['role']);
}

// Check if user has specific role
function hasRole($role) {
    return isset($_SESSION['role']) && $_SESSION['role'] === $role;
}

// Redirect if not logged in
function requireLogin() {
    if (!isLoggedIn()) {
        header("Location: /pages/login.php");
        exit();
    }
}

// Redirect if not authorized for a specific role
function requireRole($role) {
    if (!isLoggedIn() || !hasRole($role)) {
        // If logged in but wrong role, log them out
        if (isLoggedIn()) {
            session_unset();
            session_destroy();
        }
        header("Location: /pages/login.php?error=unauthorized");
        exit();
    }
}

// Get user info from database based on role and ID
function getUserInfo($conn, $role, $user_id) {
    $table = "";
    $id_field = "";

    switch ($role) {
        case 'student':
            $table = "students";
            $id_field = "student_id";
            break;
        case 'admin':
            $table = "admins";
            $id_field = "admin_id";
            break;
        case 'trainer':
            $table = "trainers";
            $id_field = "trainer_id";
            break;
        default:
            return null;
    }

    $stmt = $conn->prepare("SELECT * FROM $table WHERE $id_field = ?");
    $stmt->bind_param("s", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        return $result->fetch_assoc();
    }
    
    return null;
}

// Authenticate user
function authenticateUser($conn, $email, $password) {
    // Try student authentication
    $stmt = $conn->prepare("SELECT * FROM students WHERE email_id = ? AND flag = 1");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return [
                'role' => 'student',
                'id' => $user['student_id'],
                'name' => $user['first_name'] . ' ' . $user['last_name']
            ];
        }
    }
    
    // Try admin authentication
    $stmt = $conn->prepare("SELECT * FROM admins WHERE (username = ? OR email = ?)");
    $stmt->bind_param("ss", $email, $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return [
                'role' => 'admin',
                'id' => $user['admin_id'],
                'name' => $user['name']
            ];
        }
    }
    
    // Try trainer authentication
    $stmt = $conn->prepare("SELECT * FROM trainers WHERE email_id = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();
        if (password_verify($password, $user['password'])) {
            return [
                'role' => 'trainer',
                'id' => $user['trainer_id'],
                'name' => $user['first_name'] . ' ' . $user['last_name']
            ];
        }
    }
    
    return null;
}

// Function to generate a secure random token
function generateToken($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

// Clean input function to prevent XSS
function cleanInput($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}
?>
