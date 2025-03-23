<?php
include '../includes/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'];
    $code = $_POST['code'];

    $stmt = $conn->prepare("SELECT * FROM students WHERE email_id = ? AND flag = 0");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $conn->query("UPDATE students SET flag = 1 WHERE email_id = '$email'");
        header("Location: login.php");
    } else {
        echo "Invalid verification code";
    }
}
?>

<!-- HTML Form -->
<form method="POST">
    <input type="hidden" name="email" value="<?php echo $_GET['email']; ?>">
    <input type="text" name="code" required placeholder="Enter Verification Code">
    <button type="submit">Verify</button>
</form>
