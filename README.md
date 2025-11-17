# Laravel School Management System

A complete school management system built with Laravel 12 and **pure Vanilla JavaScript** - no npm required!

## Key Features

### No Build Tools Needed!
- ✅ **No npm install** required
- ✅ **No build process** (webpack, vite, etc.)
- ✅ **Pure JavaScript** (React-like component system)
- ✅ **Pure CSS** (No SASS/LESS)
- ✅ **Direct cPanel deployment**

### Functionality
- 👨‍🎓 Student Management (Add, Edit, Delete, View)
- 👨‍🏫 Teacher Management
- 🏫 Class Management
- 📚 Subject Management
- 📝 Attendance Tracking
- 📊 Grades/Results Management
- 📈 Dashboard with Statistics
- 🔐 Authentication System

## Tech Stack

**Backend:**
- Laravel 12
- MySQL/MariaDB
- RESTful API Architecture

**Frontend:**
- Vanilla JavaScript (ES6+)
- Custom React-like Framework
- Pure CSS3
- Modern Responsive Design

## Quick Start

### Installation

1. Clone the repository:
```bash
git clone <repository-url>
cd school-management-system-claude
```

2. Install PHP dependencies:
```bash
composer install
```

3. Set up environment:
```bash
cp .env.example .env
php artisan key:generate
```

4. Configure database in `.env`:
```env
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_password
```

5. Run migrations:
```bash
php artisan migrate
```

6. Create admin user:
```bash
php artisan tinker
```
Then:
```php
User::create([
    'name' => 'Admin',
    'email' => 'admin@school.com',
    'password' => Hash::make('admin123'),
    'role' => 'admin'
]);
```

7. Start the server:
```bash
php artisan serve
```

8. Visit: `http://localhost:8000`

## Project Structure

```
├── app/
│   ├── Http/Controllers/Api/    # API Controllers
│   └── Models/                  # Eloquent Models
├── database/
│   └── migrations/              # Database Migrations
├── public/
│   ├── css/app.css             # Pure CSS Styles
│   └── js/
│       ├── framework.js        # React-like Framework
│       ├── components.js       # UI Components
│       └── app.js              # Application Entry Point
├── resources/views/
│   └── app.blade.php           # Main HTML Template
└── routes/web.php              # All Routes
```

## Features in Detail

### Student Management
- Add new students with complete information
- Track admission details
- Assign to classes
- Parent/Guardian information
- Medical information
- Status tracking (Active/Inactive/Graduated)

### Teacher Management
- Employee records
- Qualification tracking
- Subject assignment
- Class teacher assignment
- Salary management

### Attendance System
- Daily attendance marking
- Status: Present, Absent, Late, Excused
- Date-wise tracking
- Student-wise reports

### Grades/Results
- Multiple exam types support
- Marks entry per subject
- Automatic percentage calculation
- Grade assignment
- Remarks and comments

### Dashboard
- Total students count
- Active students
- Total teachers
- Total classes
- Today's attendance summary

## cPanel Deployment

See [DEPLOYMENT.md](DEPLOYMENT.md) for complete cPanel deployment instructions.

### Quick Deploy Steps:
1. Upload files to cPanel
2. Create database
3. Configure `.env`
4. Run `composer install`
5. Run `php artisan migrate`
6. Set document root to `public` folder
7. Done! ✅

## API Endpoints

### Authentication
- `POST /api/auth/login` - User login
- `POST /api/auth/logout` - User logout
- `GET /api/auth/user` - Get authenticated user

### Students
- `GET /api/students` - List all students
- `POST /api/students` - Create new student
- `GET /api/students/{id}` - Get student details
- `PUT /api/students/{id}` - Update student
- `DELETE /api/students/{id}` - Delete student

### Teachers
- `GET /api/teachers` - List all teachers
- `POST /api/teachers` - Create new teacher
- `GET /api/teachers/{id}` - Get teacher details
- `PUT /api/teachers/{id}` - Update teacher
- `DELETE /api/teachers/{id}` - Delete teacher

### Classes
- `GET /api/classes` - List all classes
- `POST /api/classes` - Create new class
- `GET /api/classes/{id}` - Get class details
- `PUT /api/classes/{id}` - Update class
- `DELETE /api/classes/{id}` - Delete class

### Subjects
- `GET /api/subjects` - List all subjects
- `POST /api/subjects` - Create new subject
- `GET /api/subjects/{id}` - Get subject details
- `PUT /api/subjects/{id}` - Update subject
- `DELETE /api/subjects/{id}` - Delete subject

### Attendance
- `GET /api/attendance` - List attendance records
- `POST /api/attendance` - Mark attendance
- `GET /api/attendance/{id}` - Get attendance record
- `PUT /api/attendance/{id}` - Update attendance
- `DELETE /api/attendance/{id}` - Delete attendance

### Grades
- `GET /api/grades` - List all grades
- `POST /api/grades` - Create new grade
- `GET /api/grades/{id}` - Get grade details
- `PUT /api/grades/{id}` - Update grade
- `DELETE /api/grades/{id}` - Delete grade

## Database Schema

### Users
- id, name, email, password, role, phone, address

### Students
- id, user_id, admission_number, admission_date, class_id, roll_number, date_of_birth, gender, blood_group, parent details, medical_info, photo, status

### Teachers
- id, user_id, employee_id, joining_date, qualification, salary, specialization, date_of_birth, gender, photo, status

### Classes
- id, name, section, capacity, class_teacher_id, description, status

### Subjects
- id, name, code, class_id, teacher_id, description, total_marks, passing_marks, type, status

### Grades
- id, student_id, subject_id, exam_type, marks_obtained, total_marks, grade, remarks, exam_date

### Attendance
- id, student_id, date, status, remarks

## Browser Support

- Chrome 90+
- Firefox 88+
- Safari 14+
- Edge 90+

## Security

- CSRF Protection
- SQL Injection Prevention (Eloquent ORM)
- XSS Protection
- Password Hashing (Bcrypt)
- Session Management

## Contributing

1. Fork the repository
2. Create your feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is open-source and available under the MIT License.

## Author

Built with ❤️ using Laravel and Vanilla JavaScript

## Support

For issues and questions:
- Check DEPLOYMENT.md for deployment help
- Review Laravel documentation
- Open an issue on GitHub

---

**Remember:** This project requires **NO npm install** - it's ready to deploy on cPanel as-is!
