<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard</title>
    <link rel="stylesheet" href="\css\student_bashboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php
    session_start();
    if (!isset($_SESSION['name'])) {
        header("Location: login.php");
        exit();
    }
    ?>
    <div class="dashboard-container">
        <aside class="sidebar">
            <div class="profile">
                <img src="\pages\images\1698363975359.jpg" alt="Profile Picture">
                <h3><?php echo $_SESSION['name']; ?></h3>
                <p>Student</p>
            </div>
            <nav>
                <ul>
                    <li><a href="#"><i class="fas fa-home"></i> Dashboard</a></li>
                    <li><a href="profile.php"><i class="fas fa-user"></i> Profile</a></li>
                    <li><a href="courses.php"><i class="fas fa-book"></i> Courses</a></li>
                    <li><a href="schedule.php"><i class="fas fa-calendar"></i> Schedule</a></li>
                    <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
                </ul>
            </nav>
        </aside>
        <main class="main-content">
            <header>
                <h1>Welcome, <?php echo $_SESSION['name']; ?>!</h1>
            </header>
            <section class="dashboard-cards">
                <div class="card">
                    <i class="fas fa-book"></i>
                    <h2>5</h2>
                    <p>Enrolled Courses</p>
                </div>
                <div class="card">
                    <i class="fas fa-calendar-check"></i>
                    <h2>10</h2>
                    <p>Completed Lessons</p>
                </div>
                <div class="card">
                    <i class="fas fa-tasks"></i>
                    <h2>3</h2>
                    <p>Pending Assignments</p>
                </div>
                <div class="card">
                    <i class="fas fa-clock"></i>
                    <h2>15</h2>
                    <p>Hours Spent</p>
                </div>
            </section>
        </main>
    </div>
</body>
</html>
