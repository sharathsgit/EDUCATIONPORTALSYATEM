<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

$student_id = $_SESSION['student_id'];
$stmt = $conn->prepare("SELECT * FROM students WHERE student_id = ?");
$stmt->bind_param("s", $student_id);
$stmt->execute();
$result = $stmt->get_result();
$student = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="stylesheet" href="../css/styles1.css">
    <style>
        body {
    margin: 0;
    font-family: Arial, sans-serif;
    background-color: #f4f4f4;
}

.container {
    width: 80%;
    margin: 20px auto;
    background: white;
    padding: 20px;
    border-radius: 10px;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    text-align: center;
}

h2 {
    color: #2c3e50;
    margin-bottom: 20px;
}

.table-container {
    overflow-x: auto;
}

.table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
}

.table th, .table td {
    border: 1px solid #ddd;
    padding: 12px;
    text-align: left;
}

.table th {
    background-color: #2c3e50;
    color: white;
}

button {
    background-color: #1abc9c;
    color: white;
    border: none;
    padding: 10px 15px;
    margin: 10px 5px;
    cursor: pointer;
    border-radius: 5px;
    font-size: 16px;
}

button:hover {
    background-color: #16a085;
}

    </style>
</head>
<body>
    <div class="container">
        <h2>Student Profile</h2>
        <p><strong>Name:</strong> <?php echo $student['first_name'] . " " . $student['last_name']; ?></p>
        <p><strong>Email:</strong> <?php echo $student['email_id']; ?></p>
        <p><strong>Phone:</strong> <?php echo $student['phone_no']; ?></p>
        <p><strong>City:</strong> <?php echo $student['city']; ?></p>
        <p><strong>State:</strong> <?php echo $student['state']; ?></p>
        <p><strong>District:</strong> <?php echo $student['district']; ?></p>
        <p><strong>Address:</strong> <?php echo $student['address']; ?></p>
        <br>
        <a href="welcome.php"><button>Back</button></a>
        <a href="logout.php"><button>Logout</button></a>
    </div>
    
</body>
</html>
