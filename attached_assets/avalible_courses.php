<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Fetch Available Courses
$courses = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Available Courses</title>
    <link rel="stylesheet" href="../css/admin_dashboard.css">
    <style>
        .styled-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 18px;
            text-align: left;
        }
        .styled-table th, .styled-table td {
            padding: 10px;
            border-bottom: 1px solid #ddd;
        }
        .styled-table th {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Available Courses</h2>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $courses->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
    </div>
    <button class="back-btn"><a href="dashboard.php">🔙 Back</a></button>
</body>
</html>
