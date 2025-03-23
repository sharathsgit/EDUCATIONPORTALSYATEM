<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

if (isset($_GET['id'])) {
    $course_id = $_GET['id'];
    $result = $conn->query("SELECT * FROM courses WHERE course_id = $course_id");
    $course = $result->fetch_assoc();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];

    $stmt = $conn->prepare("UPDATE courses SET course_name = ? WHERE course_id = ?");
    $stmt->bind_param("si", $course_name, $course_id);
    $stmt->execute();
    header("Location: courses.php");
    exit();
}
?>

<!-- HTML Form -->
<form method="POST">
    <input type="hidden" name="course_id" value="<?php echo $course['course_id']; ?>">
    <input type="text" name="course_name" value="<?php echo $course['course_name']; ?>" required>
    <button type="submit">Update Course</button>
</form>
<a href="courses.php"><button>Back</button></a>
