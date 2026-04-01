# Smart-Course-Registration-And-Prerequisite-Validation-System

# Overview
The Smart Course Registration System is a PHP and MySQL-based web application designed to automate and streamline the student registration process. It ensures students meet course prerequisites, adhere to credit limits, and avoid timetable conflicts. The system includes both a student dashboard for course management and an administrative dashboard for overseeing courses.

## Features
- **User Authentication:** Secure login and registration for both students and administrators.
- **Role-based Access:** Differentiates between 'student' and 'admin' functionalities.
- **Smart Validation:**
  - Automatically checks if students have passed prerequisite courses.
  - Enforces maximum credit limits per semester (default 18 credits).
  - Prevents timetable scheduling conflicts.
- **Course Management:** Admins can create courses, set capacities, schedules, and prerequisites.
- **Dynamic Dashboard:** Real-time updates via API endpoints using vanilla JavaScript (Fetch API).

- ## Technology Stack
- **Frontend:** HTML, CSS, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Architecture:** Client-Server architecture with AJAX-based API endpoints.

- ## Database Schema
The system uses the following main tables:
- `users`: Stores student and admin credentials and roles.
- `courses`: Stores course details, schedule, and capacity.
- `prerequisites`: Maps courses to their required prerequisites.
- `completed_courses`: Tracks the academic history of students.
- `registrations`: Active course enrollments for the current semester.

- ## Installation & Setup
1. Clone the repository to your local web server environment (e.g., inside `htdocs` for XAMPP).
2. Start Apache and MySQL from your XAMPP/WAMP control panel.
3. Import the `database.sql` file into your MySQL server to set up the database structure and sample data.
4. Ensure your database connection settings in `config/` (or relative connection files) match your local MySQL credentials.
5. Access the application in your browser (e.g., `http://localhost/smart-course-registration`).

**Default Admin Credentials:**
- Email: `admin@smartreg.edu`
- Password: `admin123`

- ## Screenshots
- **1.Login Page**
- <img width="959" height="525" alt="image" src="https://github.com/user-attachments/assets/d86dd98f-0040-4dad-a181-7c3058895fc7" />

<img width="959" height="539" alt="image" src="https://github.com/user-attachments/assets/c42a4c14-924f-494b-aa90-3cab3214c34e" />

**2.Student Dasboard**
<img width="959" height="539" alt="image" src="https://github.com/user-attachments/assets/ef7e0617-9e20-4c73-9e9a-3e3a5a4bbc4f" />

**3.Admin Dasboard**
<img width="959" height="539" alt="image" src="https://github.com/user-attachments/assets/49e0028e-9d18-4ff0-b901-227aa4c030f0" />

<img width="959" height="539" alt="image" src="https://github.com/user-attachments/assets/ca45156d-63c2-453a-8dba-3c19f719833f" />


