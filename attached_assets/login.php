<?php
session_start();
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE email_id = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if ($user && password_verify($password, $user['password']) && $user['flag'] == 1) {
        $_SESSION['student_id'] = $user['student_id'];
        $_SESSION['name'] = $user['first_name'];
        header("Location:welcome.php");
        exit();
    } else {
        $error = "Invalid login credentials or account not verified.";
    }
}
?>

<!-- HTML Form -->
 <head>
    <link rel="stylesheet" href="/css/styles.css">
 </head>
<form method="POST">
    <h2>Login</h2>
    <?php if (isset($error)) echo "<p style='color: red;'>$error</p>"; ?>
    <input type="email" name="email" required placeholder="Email">
    <input type="password" name="password" required placeholder="Password">
    <button type="submit">Login</button>
</form>
