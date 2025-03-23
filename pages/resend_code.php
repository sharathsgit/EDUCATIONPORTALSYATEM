<?php
session_start();
include '../includes/db.php';
include '../includes/mailer.php';

// Check if verification email session exists
if (!isset($_SESSION['verification_email'])) {
    header("Location: register.php");
    exit();
}

$email = $_SESSION['verification_email'];

// Generate a new verification code
$new_verification_code = rand(100000, 999999);
$_SESSION['verification_code'] = $new_verification_code;

// Send the new code
if (sendVerificationEmail($email, $new_verification_code)) {
    $_SESSION['resend_success'] = true;
} else {
    $_SESSION['resend_error'] = true;
}

// Redirect back to verify page
header("Location: verify.php");
exit();
?>
