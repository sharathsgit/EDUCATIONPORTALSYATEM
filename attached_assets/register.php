<?php
include '../includes/db.php';
include '../includes/mailer.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $first_name = $_POST['first_name'];
    $last_name = $_POST['last_name'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $phone = $_POST['phone'];
    $address = $_POST['address'];
    $state = $_POST['state'];
    $district = $_POST['district'];
    $city = $_POST['city'];
    $alt_email = $_POST['alt_email'];
    $alt_phone = $_POST['alt_phone'];
    $student_id = "STU" . rand(1000, 9999);
    $loginid = strtolower($first_name . rand(10, 99));
    $verification_code = rand(100000, 999999);

    $stmt = $conn->prepare("INSERT INTO students (student_id, first_name, last_name, loginid, password, phone_no, address, state, district, city, email_id, alternate_email_id, alternate_phone_no, flag) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 0)");
    $stmt->bind_param("sssssssssssss", $student_id, $first_name, $last_name, $loginid, $password, $phone, $address, $state, $district, $city, $email, $alt_email, $alt_phone);
    
    if ($stmt->execute() && sendVerificationEmail($email, $verification_code)) {
        header("Location: verify.php?email=$email");
        exit();
    } else {
        echo "Error registering user";
    }
}
?>

<!-- HTML Form -->
 <html>
    <title>student registration form</title>
    <head>
        <link rel="stylesheet" href="/css/styles.css">
        
    </head>
<form method="POST">
    <center><h2>Registration form</h2></center>
    <input type="text" name="first_name" required placeholder="First Name">
    <input type="text" name="last_name" required placeholder="Last Name">
    <input type="email" name="email" required placeholder="Email">
    <input type="password" name="password" required placeholder="Password">
    <input type="text" name="phone" required placeholder="Phone Number">
    <input type="text" name="address" placeholder="Address">
    <input type="text" name="state" placeholder="State">
    <input type="text" name="district" placeholder="District">
    <input type="text" name="city" placeholder="City">
    <input type="email" name="alt_email" placeholder="Alternate Email">
    <input type="text" name="alt_phone" placeholder="Alternate Phone Number">
    <button type="submit">Proceed</button>
</form>
<!-- Link the JavaScript file -->
<script src="js/script.js"></script>


</html>
