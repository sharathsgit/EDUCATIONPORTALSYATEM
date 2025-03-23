<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM courses");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Courses</title>
    <link rel="stylesheet" href="../assets/styles.css">
</head>
<body>
    <div class="container">
        <h2>Course List</h2>
        <table border="1">
            <tr>
                <th>Course ID</th>
                <th>Course Name</th>
                <th>Actions</th>
            </tr>
            <?php while ($course = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $course['course_id']; ?></td>
                    <td><?php echo $course['course_name']; ?></td>
                    <td><a href="edit_course.php?id=<?php echo $course['course_id']; ?>">Edit</a></td>
                </tr>
            <?php } ?>
        </table>
        <a href="dashboard.php"><button>Back</button></a>
    </div>
</body>
</html>
