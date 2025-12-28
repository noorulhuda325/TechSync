-- TeachSync (EduTrack) Database Schema
-- School Management System Database

CREATE DATABASE IF NOT EXISTS teachsync CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE teachsync;

-- Users table (for authentication)
CREATE TABLE IF NOT EXISTS users (
    id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'teacher', 'student') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Departments table
CREATE TABLE IF NOT EXISTS departments (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Rooms table
CREATE TABLE IF NOT EXISTS rooms (
    id INT PRIMARY KEY AUTO_INCREMENT,
    room_number VARCHAR(20) UNIQUE NOT NULL,
    building VARCHAR(50),
    capacity INT,
    room_type VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Admins table
CREATE TABLE IF NOT EXISTS admins (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    admin_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Teachers table
CREATE TABLE IF NOT EXISTS teachers (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    teacher_id VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    department_id INT,
    phone VARCHAR(20),
    address TEXT,
    hire_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Classes table
CREATE TABLE IF NOT EXISTS classes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_code VARCHAR(20) UNIQUE NOT NULL,
    class_name VARCHAR(100) NOT NULL,
    department_id INT NOT NULL,
    teacher_id INT NOT NULL,
    room_id INT,
    credits INT DEFAULT 3,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (department_id) REFERENCES departments(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Students table
CREATE TABLE IF NOT EXISTS students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT UNIQUE NOT NULL,
    student_id VARCHAR(20) UNIQUE NOT NULL,
    roll_number VARCHAR(20) UNIQUE NOT NULL,
    full_name VARCHAR(100) NOT NULL,
    class_id INT,
    section VARCHAR(10),
    phone VARCHAR(20),
    address TEXT,
    enrollment_date DATE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Class-Student Enrollment (Many-to-Many)
CREATE TABLE IF NOT EXISTS class_students (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    student_id INT NOT NULL,
    enrollment_date DATE DEFAULT (CURRENT_DATE),
    status ENUM('active', 'dropped') DEFAULT 'active',
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    UNIQUE KEY unique_enrollment (class_id, student_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Schedule/Timetable table
CREATE TABLE IF NOT EXISTS schedules (
    id INT PRIMARY KEY AUTO_INCREMENT,
    class_id INT NOT NULL,
    day_of_week ENUM('Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday') NOT NULL,
    hour_number INT NOT NULL CHECK (hour_number BETWEEN 1 AND 6),
    room_id INT,
    start_time TIME,
    end_time TIME,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (room_id) REFERENCES rooms(id) ON DELETE SET NULL,
    UNIQUE KEY unique_schedule (day_of_week, hour_number, room_id),
    INDEX idx_class_day_hour (class_id, day_of_week, hour_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Attendance table
CREATE TABLE IF NOT EXISTS attendance (
    id INT PRIMARY KEY AUTO_INCREMENT,
    student_id INT NOT NULL,
    class_id INT NOT NULL,
    attendance_date DATE NOT NULL,
    hour_number INT NOT NULL CHECK (hour_number BETWEEN 1 AND 6),
    status ENUM('P', 'A', 'L') NOT NULL COMMENT 'P=Present, A=Absent, L=Late',
    marked_by INT NOT NULL COMMENT 'Teacher ID who marked',
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
    FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    FOREIGN KEY (marked_by) REFERENCES teachers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_attendance (student_id, class_id, attendance_date, hour_number),
    INDEX idx_date_class (attendance_date, class_id),
    INDEX idx_student_date (student_id, attendance_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Events table
CREATE TABLE IF NOT EXISTS events (
    id INT PRIMARY KEY AUTO_INCREMENT,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    event_date DATE NOT NULL,
    event_time TIME,
    event_type ENUM('exam', 'meeting', 'holiday', 'other') DEFAULT 'other',
    created_by INT NOT NULL COMMENT 'Admin ID',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (created_by) REFERENCES admins(id) ON DELETE CASCADE,
    INDEX idx_event_date (event_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert Sample Data

-- Default Admin User (password: admin123)
INSERT INTO users (email, password, role) VALUES 
('admin@teachsync.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin');

INSERT INTO admins (user_id, admin_id, full_name, phone) VALUES 
(1, 'ADMIN-001', 'System Administrator', '1234567890');

-- Sample Departments
INSERT INTO departments (name, code, description) VALUES 
('Computer Science', 'CS', 'Department of Computer Science and Information Technology'),
('Mathematics', 'MATH', 'Department of Mathematics'),
('Physics', 'PHY', 'Department of Physics'),
('Chemistry', 'CHEM', 'Department of Chemistry'),
('English', 'ENG', 'Department of English Language and Literature');

-- Sample Rooms
INSERT INTO rooms (room_number, building, capacity, room_type) VALUES 
('R-101', 'Main Building', 40, 'Lecture Hall'),
('R-102', 'Main Building', 35, 'Lecture Hall'),
('R-201', 'Main Building', 30, 'Classroom'),
('R-202', 'Main Building', 30, 'Classroom'),
('LAB-301', 'Science Building', 25, 'Laboratory'),
('LAB-302', 'Science Building', 25, 'Laboratory');

-- Sample Teachers
INSERT INTO users (email, password, role) VALUES 
('teacher1@teachsync.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('teacher2@teachsync.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher'),
('teacher3@teachsync.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'teacher');

INSERT INTO teachers (user_id, teacher_id, full_name, department_id, phone, hire_date) VALUES 
(2, 'T-4592', 'Dr. Sarah Johnson', 1, '1111111111', '2020-01-15'),
(3, 'T-4593', 'Prof. Michael Chen', 2, '2222222222', '2019-08-20'),
(4, 'T-4594', 'Dr. Emily Davis', 3, '3333333333', '2021-03-10');

-- Sample Classes
INSERT INTO classes (class_code, class_name, department_id, teacher_id, room_id, credits) VALUES 
('CS-101', 'Introduction to Programming', 1, 1, 1, 3),
('MATH-201', 'Calculus I', 2, 2, 2, 4),
('PHY-201', 'Physics Fundamentals', 3, 3, 5, 3),
('CS-301', 'Database Systems', 1, 1, 1, 3);

-- Sample Students
INSERT INTO users (email, password, role) VALUES 
('student1@teachsync.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('student2@teachsync.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student'),
('student3@teachsync.edu', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'student');

INSERT INTO students (user_id, student_id, roll_number, full_name, class_id, section, enrollment_date) VALUES 
(5, 'STU-2024-001', 'R001', 'John Smith', 1, 'A', '2024-01-15'),
(6, 'STU-2024-002', 'R002', 'Alice Brown', 1, 'A', '2024-01-15'),
(7, 'STU-2024-003', 'R003', 'Bob Wilson', 2, 'B', '2024-01-15');

-- Enroll students in classes
INSERT INTO class_students (class_id, student_id) VALUES 
(1, 1), (1, 2), (2, 3), (3, 1), (3, 2);

-- Sample Schedule
INSERT INTO schedules (class_id, day_of_week, hour_number, room_id, start_time, end_time) VALUES 
(1, 'Monday', 1, 1, '09:00:00', '10:00:00'),
(1, 'Wednesday', 2, 1, '10:00:00', '11:00:00'),
(1, 'Friday', 3, 1, '11:00:00', '12:00:00'),
(2, 'Tuesday', 1, 2, '09:00:00', '10:30:00'),
(2, 'Thursday', 2, 2, '10:00:00', '11:30:00'),
(3, 'Monday', 4, 5, '13:00:00', '14:00:00'),
(3, 'Wednesday', 5, 5, '14:00:00', '15:00:00');

-- Sample Attendance
INSERT INTO attendance (student_id, class_id, attendance_date, hour_number, status, marked_by) VALUES 
(1, 1, CURDATE(), 1, 'P', 1),
(2, 1, CURDATE(), 1, 'P', 1),
(1, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 'L', 1),
(2, 1, DATE_SUB(CURDATE(), INTERVAL 1 DAY), 1, 'P', 1);

-- Sample Events
INSERT INTO events (title, description, event_date, event_time, event_type, created_by) VALUES 
('Midterm Exam - CS 101', 'Midterm examination for Introduction to Programming', DATE_ADD(CURDATE(), INTERVAL 7 DAY), '09:00:00', 'exam', 1),
('Faculty Meeting', 'Monthly faculty meeting', DATE_ADD(CURDATE(), INTERVAL 3 DAY), '14:00:00', 'meeting', 1),
('Spring Break', 'Spring break holiday', DATE_ADD(CURDATE(), INTERVAL 14 DAY), NULL, 'holiday', 1);

