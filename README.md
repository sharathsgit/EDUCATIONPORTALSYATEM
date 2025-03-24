# EDUCATION PORTAL SYSTEM

## Project Overview
The Education Portal System is a web-based platform that allows students to register, enroll in courses, and interact with trainers. Admins can manage students, courses, and trainer schedules through a dedicated dashboard. The system ensures secure authentication with email OTP verification and maintains a structured database for user management.

## Features
- **Student Registration & Login** with OTP verification
- **Admin Dashboard** to manage students, courses, and trainers
- **Trainer Management** for scheduling and course assignments
- **Course Enrollment System** with an option to integrate Razorpay for payments
- **Consistent UI Design** with a single CSS file for all pages
- **Dashboard-based Navigation** for students and admins

## Installation & Setup
### Prerequisites
- PHP (>=7.4)
- MySQL Database
- Apache Server (XAMPP/LAMP/WAMP recommended)
- Composer (for dependency management)

### Steps to Install
1. Clone the repository from GitHub:
   ```sh
   git clone https://github.com/yourusername/education-portal.git
   ```
2. Move into the project directory:
   ```sh
   cd education-portal
   ```
3. Set up the database:
   - Create a MySQL database.
   - Import `database.sql` from the `db/` folder into your database.
4. Update database connection settings in `config.php`.
5. Start the Apache server and access the project via `http://localhost/education-portal`.

## Usage Guide
- **Students:** Register, verify email, log in, browse available courses, and enroll.
- **Admins:** Log in, manage students, courses, and trainers via the admin dashboard.
- **Trainers:** View and manage assigned courses and schedules.

## Project Structure
```
/education-portal
├── admin/            # Admin panel files
├── assets/           # CSS, JS, images
├── db/               # Database scripts
├── includes/         # Common PHP files (config, functions)
├── student/          # Student-related pages
├── trainer/          # Trainer-related pages
├── index.php         # Entry point
├── config.php        # Database configuration
└── README.md         # Project documentation
```

## Technology Stack
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP (Core PHP)
- **Database:** MySQL
- **Payment Gateway (Optional):** Razorpay

## Contributing
1. Fork the repository.
2. Create a feature branch (`git checkout -b feature-name`).
3. Commit changes (`git commit -m "Add feature"`).
4. Push to the branch (`git push origin feature-name`).
5. Open a Pull Request.

## License
This project is licensed under the MIT License.

