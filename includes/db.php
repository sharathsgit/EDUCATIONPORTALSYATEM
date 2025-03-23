<?php
// Database configuration
$host = "localhost";
$username = "root";
$password = "";
$dbname = "educational_management";

// Create database connection
$conn = new mysqli($host, $username, $password, $dbname);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create tables if they don't exist
function create_tables($conn) {
    // Students Table
    $sql_students = "CREATE TABLE IF NOT EXISTS students (
        student_id VARCHAR(10) PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        loginid VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone_no VARCHAR(15) NOT NULL,
        address TEXT,
        state VARCHAR(50),
        district VARCHAR(50),
        city VARCHAR(50),
        email_id VARCHAR(100) NOT NULL UNIQUE,
        alternate_email_id VARCHAR(100),
        alternate_phone_no VARCHAR(15),
        flag TINYINT(1) DEFAULT 0,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql_students);

    // Admin Table
    $sql_admin = "CREATE TABLE IF NOT EXISTS admins (
        admin_id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(50) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        name VARCHAR(100) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql_admin);

    // Trainers Table
    $sql_trainers = "CREATE TABLE IF NOT EXISTS trainers (
        trainer_id VARCHAR(10) PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        loginid VARCHAR(50) NOT NULL,
        password VARCHAR(255) NOT NULL,
        expertise VARCHAR(100) NOT NULL,
        phone_no VARCHAR(15) NOT NULL,
        email_id VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->query($sql_trainers);

    // Courses Table
    $sql_courses = "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(100) NOT NULL,
        description TEXT,
        price DECIMAL(10,2) NOT NULL,
        trainer_id VARCHAR(10),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (trainer_id) REFERENCES trainers(trainer_id) ON DELETE SET NULL
    )";
    $conn->query($sql_courses);

    // Schedules Table
    $sql_schedules = "CREATE TABLE IF NOT EXISTS schedules (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_id INT,
        trainer_id VARCHAR(10),
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        start_time TIME NOT NULL,
        end_time TIME NOT NULL,
        days VARCHAR(50) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
        FOREIGN KEY (trainer_id) REFERENCES trainers(trainer_id) ON DELETE CASCADE
    )";
    $conn->query($sql_schedules);

    // Enrollments Table
    $sql_enrollments = "CREATE TABLE IF NOT EXISTS enrollments (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id VARCHAR(10) NOT NULL,
        course_id INT NOT NULL,
        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        status ENUM('active', 'completed', 'dropped') DEFAULT 'active',
        FOREIGN KEY (student_id) REFERENCES students(student_id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    )";
    $conn->query($sql_enrollments);

    // Insert default admin if not exists
    $check_admin = $conn->query("SELECT * FROM admins LIMIT 1");
    if ($check_admin->num_rows == 0) {
        $admin_password = password_hash("admin123", PASSWORD_DEFAULT);
        $sql_insert_admin = "INSERT INTO admins (username, password, email, name) 
                             VALUES ('admin', '$admin_password', 'admin@example.com', 'System Administrator')";
        $conn->query($sql_insert_admin);
    }
}

// Call the function to create tables
create_tables($conn);
?>
