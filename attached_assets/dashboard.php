<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="\css\student_bashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

<style>#content-area {
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
</style>

</head>
<body>
    <?php
    session_start();
    include '../includes/db.php';
    if (!isset($_SESSION['admin'])) {
        header("Location: login.php");
        exit();
    }

    // Fetch total students count
    $stmt = $conn->prepare("SELECT COUNT(*) AS total_students FROM students");
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $total_students = $row['total_students'];
    ?>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="profile">
                <img src="\pages\images\admin_profile.jpg" alt="Profile Picture">
                <h3><?php echo $_SESSION['admin']; ?></h3>
                <p>Administrator</p>
            </div>
            <nav>
            <ul>
    <li><a href="#" onclick="loadPage('student.php')">Student Management</a></li>
    <li><a href="#" onclick="loadPage('course_management.php')">Course Management</a></li>
    <li><a href="#" onclick="loadPage('schedule_management.php')">Schedule Management</a></li>
    <li><a href="#" onclick="loadPage('logout.php')">Logout</a></li>
</ul>

            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Welcome, <?php echo $_SESSION['admin']; ?>!</h1>
            </header>

            <div id="content-area" class="fade-in">
    
    <div class="content-box">
        
    <section class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-user-graduate"></i>
                    <h2><?php echo $total_students; ?></h2>
                    <a href="#" onclick="loadPage('student.php')">Toatal Students</a>
                </div>
                <div class="card">
                    <i class="fas fa-book"></i>
                    <h2>20</h2>
                    <a href="#" onclick="loadPage('course_management.php')">Course Avilable</a>
                </div>
                <div class="card">
                    <i class="fas fa-calendar"></i>
                    <h2>15</h2>
                    <p>Scheduled Classes</p>
                </div>
                <div class="card">
                    <i class="fas fa-user"></i>
                    <h2>5</h2>
                    <p>Active Trainers</p>
                </div>
            </section>
        
    </div>
</div>

            <!-- SHOWING DYNAMIC CONTENT -->

          

        </main>
    </div>
    
    <!-- java script -->
        <script>
   function loadPage(page) {
    var xhr = new XMLHttpRequest();
    xhr.open("GET", page, true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState == 4 && xhr.status == 200) {
            var contentArea = document.getElementById("content-area");
            contentArea.classList.remove("fade-in");
            setTimeout(() => {
                contentArea.innerHTML = xhr.responseText;
                contentArea.classList.add("fade-in");
            }, 100); // Add delay for smooth transition
        }
    };
    xhr.send();
}

</script>

    
</body>
</html>
