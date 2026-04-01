-- Create the database if it doesn't exist
CREATE DATABASE IF NOT EXISTS smart_course_registration;
USE smart_course_registration;

-- Users Table (Students & Admins)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    role ENUM('student', 'admin') DEFAULT 'student',
    max_credits INT DEFAULT 18,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Courses Table
CREATE TABLE IF NOT EXISTS courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(20) NOT NULL UNIQUE,
    name VARCHAR(150) NOT NULL,
    credits INT NOT NULL,
    capacity INT NOT NULL,
    schedule_day ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday') NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Prerequisites Table (Which course requires which other course)
CREATE TABLE IF NOT EXISTS prerequisites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    course_id INT NOT NULL,
    prerequisite_course_id INT NOT NULL,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    FOREIGN KEY (prerequisite_course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Completed Courses (for checking prerequisites)
CREATE TABLE IF NOT EXISTS completed_courses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    grade VARCHAR(2),
    passed BOOLEAN DEFAULT TRUE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
);

-- Registrations Table (Current active enrollments)
CREATE TABLE IF NOT EXISTS registrations (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    course_id INT NOT NULL,
    status ENUM('registered', 'rejected', 'dropped') DEFAULT 'registered',
    registration_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE,
    UNIQUE(user_id, course_id) -- Prevent duplicate registration in the same semester
);

-- Insert a default admin user (Password: admin123)
-- Using PHP password_hash('admin123', PASSWORD_BCRYPT) gives something like below:
INSERT IGNORE INTO users (name, email, password, role) VALUES 
('System Admin', 'admin@smartreg.edu', '$2y$10$fVdZT3BMbwlKCtlVtOhwxO116EuLM0sMpjhSaS7kj0OsXez4/Z3vK', 'admin');

-- Insert some sample courses
INSERT IGNORE INTO courses (code, name, credits, capacity, schedule_day, start_time, end_time) VALUES
('CS101', 'Introduction to Programming', 3, 50, 'Monday', '09:00:00', '11:00:00'),
('CS102', 'Data Structures and Algorithms', 4, 40, 'Wednesday', '10:00:00', '12:00:00'),
('MATH101', 'Calculus I', 3, 60, 'Tuesday', '08:00:00', '10:00:00'),
('CS201', 'Database Systems', 3, 40, 'Thursday', '13:00:00', '15:00:00');

-- Insert sample prerequisites (CS102 requires CS101, CS201 requires CS101)
-- Assuming IDs are sequential from 1
INSERT IGNORE INTO prerequisites (course_id, prerequisite_course_id) VALUES
((SELECT id FROM courses WHERE code='CS102'), (SELECT id FROM courses WHERE code='CS101')),
((SELECT id FROM courses WHERE code='CS201'), (SELECT id FROM courses WHERE code='CS101'));
