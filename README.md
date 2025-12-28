# TeachSync (EduTrack) - School Management System

A complete School Management Web Application built with HTML, CSS, JavaScript, PHP, and MySQL (XAMPP). The system implements role-based access control with three user roles: Admin, Teacher, and Student.

## 🎯 Features

### 🔐 Authentication System
- Secure login with email/ID and password
- PHP session management
- Role-based access control
- Automatic redirection to role-specific dashboards

### 👨‍💼 Admin Module
- **Dashboard**: Overview statistics and recent activities
- **Manage Teachers**: Create, view, and delete teacher accounts (auto-generates Teacher IDs like T-4592)
- **Manage Students**: Create, view, and delete student accounts (auto-generates Student IDs like STU-2024-001)
- **Classes & Departments**: CRUD operations for departments and classes
- **Timetable Builder**: Create master timetable with conflict detection (no overlapping hours)
- **Attendance Analytics**: Campus-wide attendance reports and statistics
- **Manage Rooms**: Add and manage classroom/room assignments
- **Events**: Create and manage events (exams, meetings, holidays)

### 👩‍🏫 Teacher Module
- **Dashboard**: Overview of classes, students, and today's attendance
- **My Classes**: View all assigned classes with student counts
- **Students List**: Searchable list of students in assigned classes
- **Mark Attendance**: Mark attendance (Present/Absent/Late) by class, date, and hour
- **Weekly Schedule**: Color-coded weekly timetable

### 🎓 Student Module
- **Dashboard**: Attendance percentage, today's attendance, and upcoming events
- **My Profile**: View personal and academic information
- **Attendance History**: View attendance records with filtering options
- **Weekly Schedule**: Color-coded personal timetable
- **Calendar & Events**: View exams, meetings, and holidays

## 🗄️ Database Design

The system uses a normalized MySQL database with the following main tables:
- `users` - Authentication and user roles
- `admins` - Admin information
- `teachers` - Teacher information
- `students` - Student information
- `departments` - Department data
- `classes` - Class/course information
- `class_students` - Many-to-many enrollment relationship
- `rooms` - Room/classroom information
- `schedules` - Timetable/schedule data
- `attendance` - Attendance records (status: P, A, L)
- `events` - Events calendar

## 🚀 Installation & Setup

### Prerequisites
- XAMPP (PHP 7.4+ and MySQL)
- Web browser (Chrome, Firefox, Edge)

### Installation Steps

1. **Install XAMPP**
   - Download and install XAMPP from https://www.apachefriends.org/
   - Start Apache and MySQL services from XAMPP Control Panel

2. **Clone/Download Project**
   - Place the project folder in `C:\xampp\htdocs\noor\` (or your XAMPP htdocs directory)

3. **Create Database**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the SQL file: `database/teachsync.sql`
   - This will create the database and all required tables with sample data

4. **Configure Database Connection**
   - Edit `includes/config.php` if needed (default settings work with XAMPP):
     ```php
     define('DB_HOST', 'localhost');
     define('DB_USER', 'root');
     define('DB_PASS', '');
     define('DB_NAME', 'teachsync');
     ```

5. **Access the Application**
   - Open browser and navigate to: `http://localhost/noor/`
   - Login with demo credentials (see below)

## 🔑 Demo Credentials

### Admin
- **Email**: admin@teachsync.edu
- **Password**: password

### Teacher
- **Email**: teacher1@teachsync.edu
- **Password**: password

### Student
- **Email**: student1@teachsync.edu
- **Password**: password

## 📁 Project Structure

```
noor/
├── admin/                  # Admin module pages
│   ├── dashboard.php
│   ├── teachers.php
│   ├── students.php
│   ├── classes.php
│   ├── timetable.php
│   ├── attendance.php
│   ├── rooms.php
│   └── events.php
├── teacher/                # Teacher module pages
│   ├── dashboard.php
│   ├── classes.php
│   ├── students.php
│   ├── attendance.php
│   └── schedule.php
├── student/                # Student module pages
│   ├── dashboard.php
│   ├── profile.php
│   ├── attendance.php
│   ├── schedule.php
│   └── events.php
├── includes/               # Shared PHP files
│   ├── config.php         # Database connection & utilities
│   ├── header.php         # Common header & navigation
│   └── footer.php          # Common footer
├── assets/                 # Static assets
│   ├── css/
│   │   └── style.css      # Main stylesheet (Maroon & Slate Gray theme)
│   └── js/
│       └── main.js        # JavaScript functions
├── database/
│   └── teachsync.sql      # Database schema & sample data
├── index.php              # Login page
├── logout.php             # Logout handler
└── README.md              # This file
```

## 🎨 UI/UX Features

- **Theme**: Professional Maroon (#800020) & Slate Gray (#708090) color scheme
- **Dark Mode**: Toggle dark/light theme (saves preference)
- **Responsive Design**: Works on desktop, tablet, and mobile devices
- **Sidebar Navigation**: Collapsible sidebar with role-specific menu items
- **Color-Coded Timetable**: Visual schedule with distinct colors per class
- **Modal Forms**: Clean modal dialogs for Add/Edit actions
- **Search Functionality**: Real-time search in lists
- **Statistics Cards**: Visual dashboard cards with icons

## 🔒 Security Features

- PHP session-based authentication
- Role-based access control (RBAC)
- SQL injection prevention (prepared statements)
- XSS protection (input sanitization)
- Password hashing (bcrypt)
- Session validation on every page

## 📊 Workflow

### Data Flow
1. **Admin** creates departments, classes, teachers, students, and timetable
2. **Teacher** views assigned classes and marks attendance
3. **Student** views their attendance, schedule, and events

### Attendance Flow
1. Admin assigns students to classes
2. Teacher selects class, date, and hour
3. Teacher marks each student as Present/Absent/Late
4. System automatically updates statistics
5. Students can view their attendance history and percentage

## 🛠️ Technical Stack

- **Frontend**: HTML5, CSS3, Vanilla JavaScript
- **Backend**: PHP (Procedural)
- **Database**: MySQL (via XAMPP)
- **Server**: Apache (XAMPP)
- **No Frameworks**: Pure PHP, HTML, CSS, JavaScript

## 📝 Notes

- All passwords in demo data are hashed (default password: "password")
- Teacher IDs are auto-generated in format: T-XXXX
- Student IDs are auto-generated in format: STU-YYYY-XXX
- Timetable prevents overlapping schedules (same room, day, hour)
- Attendance can be marked for 6 hours per day (Hour 1-6)

## 🐛 Troubleshooting

### Database Connection Error
- Ensure MySQL is running in XAMPP Control Panel
- Check database credentials in `includes/config.php`
- Verify database `teachsync` exists in phpMyAdmin

### Session Issues
- Clear browser cookies and cache
- Ensure PHP sessions are enabled
- Check PHP error logs in XAMPP

### Page Not Found
- Verify project is in correct directory (`htdocs/noor/`)
- Check Apache is running
- Verify `.htaccess` is not blocking access (if exists)

## 📄 License

This project is created for educational purposes.

## 👨‍💻 Developer Notes

- Code is well-commented for easy understanding
- Follows procedural PHP style (no OOP)
- Uses mysqli extension for database operations
- Implements proper error handling
- Responsive design with mobile-first approach

---

**TeachSync (EduTrack)** - Complete School Management System
Built with ❤️ using PHP, MySQL, HTML, CSS, and JavaScript

