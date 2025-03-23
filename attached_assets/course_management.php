<?php
session_start();
include '../includes/db.php';
if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

// Add Course
if (isset($_POST['add_course'])) {
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stmt = $conn->prepare("INSERT INTO courses (course_name, description, price) VALUES (?, ?, ?)");
    $stmt->bind_param("ssd", $course_name, $description, $price);
    $stmt->execute();
}

// Edit Course
if (isset($_POST['edit_course'])) {
    $course_id = $_POST['course_id'];
    $course_name = $_POST['course_name'];
    $description = $_POST['description'];
    $price = $_POST['price'];
    $stmt = $conn->prepare("UPDATE courses SET course_name=?, description=?, price=? WHERE id=?");
    $stmt->bind_param("ssdi", $course_name, $description, $price, $course_id);
    $stmt->execute();
}

// Delete Course
if (isset($_POST['delete_course'])) {
    $course_id = $_POST['course_id'];
    $stmt = $conn->prepare("DELETE FROM courses WHERE id=?");
    $stmt->bind_param("i", $course_id);
    $stmt->execute();
}

// Fetch Courses
$courses = $conn->query("SELECT * FROM courses");
$students = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Courses</title>
   
    <style>
        .course-form, .inline-form {
            max-width: 400px;
            margin: 20px auto;
            padding: 20px;
            background: #f9f9f9;
            border-radius: 8px;
            box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1);
        }
        .course-form input, .course-form textarea, .course-form button,
        .inline-form input, .inline-form textarea, .inline-form button {
            width: 100%;
            padding: 10px;
            margin: 10px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .course-form button, .inline-form button {
            background: #28a745;
            color: white;
            font-size: 16px;
            cursor: pointer;
            border: none;
        }
        .course-form button:hover, .inline-form button:hover {
            background: #218838;
        }
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
        .edit-btn {
            background: #ffc107;
            color: black;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
            margin-right: 5px;
        }
        .edit-btn:hover {
            background: #e0a800;
        }
        .delete-btn {
            background: #dc3545;
            color: white;
            padding: 8px 12px;
            border: none;
            cursor: pointer;
            border-radius: 5px;
        }
        .delete-btn:hover {
            background: #c82333;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Manage Courses</h2>
        <form method="POST" class="course-form">
            <h3>Add Course</h3>
            <label for="course_name">Course Name</label>
            <input type="text" name="course_name" id="course_name" placeholder="Enter course name" required>
            
            <label for="description">Course Description</label>
            <textarea name="description" id="description" placeholder="Enter course description" required></textarea>
            
            <label for="price">Course Price</label>
            <input type="number" name="price" id="price" placeholder="Enter price" required>
            
            <button type="submit" name="add_course">Add Course</button>
        </form>
        <hr>
        <h3>Existing Courses</h3>
        <table class="styled-table">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Course Name</th>
                    <th>Description</th>
                    <th>Price</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $courses->fetch_assoc()) { ?>
                    <tr>
                        <td><?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['course_name']); ?></td>
                        <td><?php echo nl2br(htmlspecialchars($row['description'])); ?></td>
                        <td>$<?php echo number_format($row['price'], 2); ?></td>
                        <td>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="edit_course" class="edit-btn">Edit</button>
                            </form>
                            <form method="POST" class="inline-form">
                                <input type="hidden" name="course_id" value="<?php echo $row['id']; ?>">
                                <button type="submit" name="delete_course" class="delete-btn" onclick="return confirm('Are you sure?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php } ?>
            </tbody>
        </table>
        <hr>
     
    </div>
    
</body>
</html>
