# Quick Setup Guide - TeachSync

## Step-by-Step Installation

### 1. Install XAMPP
- Download from: https://www.apachefriends.org/
- Install to default location (C:\xampp)
- Start Apache and MySQL from XAMPP Control Panel

### 2. Setup Project
1. Copy the entire `noor` folder to: `C:\xampp\htdocs\`
2. Ensure the path is: `C:\xampp\htdocs\noor\`

### 3. Create Database
1. Open browser: http://localhost/phpmyadmin
2. Click "New" to create a database
3. Name it: `teachsync`
4. Click "Import" tab
5. Choose file: `database/teachsync.sql`
6. Click "Go" to import

### 4. Access Application
- Open browser: http://localhost/noor/
- Login with:
  - Admin: admin@teachsync.edu / password
  - Teacher: teacher1@teachsync.edu / password
  - Student: student1@teachsync.edu / password

## Verification Checklist

- [ ] XAMPP Apache is running (green)
- [ ] XAMPP MySQL is running (green)
- [ ] Database `teachsync` exists in phpMyAdmin
- [ ] All tables are created (users, teachers, students, etc.)
- [ ] Can access http://localhost/noor/
- [ ] Login page displays correctly
- [ ] Can login with demo credentials

## Common Issues

**Issue**: "Connection failed" error
- **Solution**: Make sure MySQL is running in XAMPP

**Issue**: "Database not found"
- **Solution**: Import the SQL file again in phpMyAdmin

**Issue**: "Page not found"
- **Solution**: Check project is in `htdocs/noor/` folder

**Issue**: "Session error"
- **Solution**: Clear browser cache and cookies

## Next Steps

1. Login as Admin
2. Explore Admin Dashboard
3. Create a new teacher or student
4. Login as Teacher and mark attendance
5. Login as Student and view attendance

Enjoy using TeachSync! 🎓

