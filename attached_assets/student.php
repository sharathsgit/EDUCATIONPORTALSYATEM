<?php
session_start();
include '../includes/db.php';

if (!isset($_SESSION['admin'])) {
    header("Location: login.php");
    exit();
}

$result = $conn->query("SELECT * FROM students");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Manage Students</title>
    
    <style>
    #content-area {
    width: 100%;
    min-height: 80vh;
    margin-top: 20px;
    padding: 20px;
    background: #ffffff;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
    transition: all 0.3s ease-in-out;
}

/* Header Styling */
#content-area h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 15px;
    border-bottom: 2px solid #007bff;
    padding-bottom: 5px;
}

/* Content Box */
.content-box {
    background: #f9f9f9;
    padding: 20px;
    border-radius: 8px;
    box-shadow: 2px 2px 15px rgba(0, 0, 0, 0.1);
}

/* Smooth loading effect */
#content-area.fade-in {
    opacity: 0;
    transform: translateY(-10px);
    animation: fadeInAnimation 0.5s ease-in-out forwards;
}

@keyframes fadeInAnimation {
    from {
        opacity: 0;
        transform: translateY(-10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
#tab {
    width: 100%;
    border-collapse: collapse;
    margin: 20px 0;
    font-size: 18px;
    text-align: left;
    background: #ffffff;
    box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.1);
    border-radius: 10px;
    overflow: hidden;
}

/* Table Header */
#tab thead {
    background-color: #007bff;
    color: #ffffff;
    font-weight: bold;
    text-transform: uppercase;
}

#tab th, #tab td {
    padding: 12px 15px;
    border-bottom: 1px solid #ddd;
}

/* Alternate Row Color */
#tab tbody tr:nth-child(even) {
    background-color: #f8f9fa;
}

/* Hover Effect */
#tab tbody tr:hover {
    background-color: #d1ecf1;
    transition: background 0.3s ease-in-out;
}

/* Fixed Header Effect */
#tab thead {
    position: sticky;
    top: 0;
    z-index: 100;
}

/* Responsive Table */
@media screen and (max-width: 768px) {
    #tab {
        font-size: 14px;
    }

    #tab th, #tab td {
        padding: 8px;
    }
}

.back-btn {
    display: inline-block;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: bold;
    color: white;
    background-color: #007bff;
    border: none;
    border-radius: 5px;
    cursor: pointer;
    transition: 0.3s;
    text-align: center;
    text-decoration: none;
}

.back-btn:hover {
    background-color: #0056b3;
}

.back-btn:active {
    background-color: #004085;
}


</style>
    
</head>
<body>
<div id="content-area" class="fade-in">
    
    <div class="content-box">
        <h2>Student List</h2>
        <table border="1" id="tab">
            <tr>
                <th>Student ID</th>
                <th>Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Verified</th>
            </tr>
            <?php while ($student = $result->fetch_assoc()) { ?>
                <tr>
                    <td><?php echo $student['student_id']; ?></td>
                    <td><?php echo $student['first_name'] . " " . $student['last_name']; ?></td>
                    <td><?php echo $student['email_id']; ?></td>
                    <td><?php echo $student['phone_no']; ?></td>
                    <td><?php echo ($student['flag'] == 1) ? 'Yes' : 'No'; ?></td>
                </tr>
            <?php } ?>
        </table>
        <button class="back-btn"><a href="dashboard.php">🔙 Back</a></button>
    </div>
    </div>
    <script>
    function goBack() {
        window.history.back();
    }
</script>

</body>
</html>
