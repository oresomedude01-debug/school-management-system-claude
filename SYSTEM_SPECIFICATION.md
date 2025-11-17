# School Management System - Complete Architectural Specification

## Table of Contents
1. [System Overview](#system-overview)
2. [Technology Stack](#technology-stack)
3. [Database Architecture](#database-architecture)
4. [Application Structure](#application-structure)
5. [Module Specifications](#module-specifications)
6. [User Flows & Journeys](#user-flows--journeys)
7. [UI/UX Design System](#uiux-design-system)
8. [Localization Strategy](#localization-strategy)
9. [Security & Access Control](#security--access-control)
10. [Deployment Guide](#deployment-guide)

---

## 1. System Overview

### 1.1 Purpose
A comprehensive School Management System built entirely with Laravel, featuring a modern, React-like UI without requiring Node.js tooling. The system is designed for direct deployment to cPanel/shared hosting environments.

### 1.2 Core Principles
- **Zero Build Tools**: No npm, no Webpack, no Vite - pure Laravel + CDN assets
- **Modern UX**: React-like interactivity using Alpine.js and Tailwind CSS via CDN
- **Bilingual**: English and Arabic support with LTR-only layout
- **cPanel Ready**: Upload, configure .env, and run
- **Smart Workflows**: Token-based enrollment, auto-generated admission numbers

### 1.3 Key Features
- Role-based dashboard (Admin, Teacher, Parent)
- Student lifecycle management
- Token-protected public enrollment
- Automated admission number generation
- Teacher and class management
- Attendance tracking
- Multilingual interface (EN/AR)

---

## 2. Technology Stack

### 2.1 Backend
- **Framework**: Laravel 10.x or 11.x
- **Database**: MySQL 8.x / MariaDB 10.x
- **Authentication**: Laravel Breeze (or custom auth with Blade)
- **Session Management**: Database-driven sessions
- **Localization**: Laravel's built-in `resources/lang` system

### 2.2 Frontend
- **Templating**: Blade components and layouts
- **CSS Framework**: Tailwind CSS 3.x (CDN)
- **JavaScript**: Alpine.js 3.x (CDN) for interactivity
- **Icons**: Heroicons or Font Awesome (CDN)
- **Charts**: Chart.js (CDN) for dashboard analytics
- **No Build Step**: All assets loaded via CDN links in Blade templates

### 2.3 CDN Resources
```html
<!-- Tailwind CSS -->
<script src="https://cdn.tailwindcss.com"></script>

<!-- Alpine.js -->
<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

<!-- Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Chart.js (for dashboard) -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.x.x/dist/chart.umd.min.js"></script>
```

---

## 3. Database Architecture

### 3.1 Core Tables & Relationships

#### users
Primary authentication table for all system users.

```
id (PK)
name (string)
email (unique, string)
password (hashed)
role (enum: admin, teacher, parent)
locale (string, default: 'en')
is_active (boolean, default: true)
email_verified_at (timestamp, nullable)
remember_token
created_at, updated_at
```

**Relationships:**
- `hasOne(Teacher)`
- `hasOne(Parent)`

---

#### students
Core student records.

```
id (PK)
admission_number (unique, string) - Auto-generated
first_name (string)
middle_name (string, nullable)
last_name (string)
full_name (generated: first + middle + last)
gender (enum: male, female)
date_of_birth (date)
place_of_birth (string, nullable)
nationality (string, nullable)
religion (string, nullable)
blood_group (string, nullable)

-- Contact Info
phone (string, nullable)
email (string, nullable)
current_address (text)
permanent_address (text, nullable)

-- Academic
class_id (FK: classes.id, nullable)
section_id (FK: sections.id, nullable)
session_year (string) - e.g., "2024-2025"
admission_date (date)
admission_year (integer) - extracted from admission_date
admission_month (integer) - extracted from admission_date
admission_position_in_month (integer) - generated
admission_position_overall (integer) - generated, auto-increment

-- Previous School Info
previous_school_name (string, nullable)
previous_school_address (text, nullable)
previous_class (string, nullable)
reason_for_transfer (text, nullable)

-- Health Info
has_allergy (boolean, default: false)
allergy_type (string, nullable)
allergy_severity (enum: mild, moderate, severe, nullable)
allergy_notes (text, nullable)
health_notes (text, nullable)

-- Token Info
registration_token_id (FK: registration_tokens.id, nullable)

-- Status
status (enum: active, inactive, graduated, transferred, default: active)
is_active (boolean, default: true)

created_at, updated_at
deleted_at (soft delete)
```

**Relationships:**
- `belongsTo(Class)`
- `belongsTo(Section)`
- `belongsTo(RegistrationToken)`
- `belongsToMany(Parents)` via `parent_student` pivot
- `hasMany(AttendanceRecords)`

---

#### registration_tokens
Token system for controlled enrollment.

```
id (PK)
token_code (unique, string, 16 chars) - e.g., "ENROLL-2024-ABCD"
status (enum: unused, used, disabled, expired, default: unused)
generated_by (FK: users.id) - admin who created it
generated_at (timestamp)
used_at (timestamp, nullable)
used_by_student_id (FK: students.id, nullable)
expires_at (timestamp, nullable)
notes (text, nullable) - admin notes
created_at, updated_at
```

**Relationships:**
- `belongsTo(User, 'generated_by')`
- `hasOne(Student, 'registration_token_id')`

---

#### parents
Parent/guardian records.

```
id (PK)
user_id (FK: users.id, nullable) - if parent has login access
first_name (string)
last_name (string)
relationship (enum: father, mother, guardian, other)
phone_primary (string)
phone_secondary (string, nullable)
email (string, nullable)
occupation (string, nullable)
address (text)
national_id (string, nullable)
is_emergency_contact (boolean, default: false)
created_at, updated_at
```

**Relationships:**
- `belongsTo(User)` (optional)
- `belongsToMany(Students)` via `parent_student` pivot

---

#### parent_student (pivot)
```
id (PK)
parent_id (FK: parents.id)
student_id (FK: students.id)
is_primary (boolean, default: false)
created_at, updated_at
```

---

#### teachers
Teacher records.

```
id (PK)
user_id (FK: users.id)
first_name (string)
last_name (string)
employee_id (unique, string)
phone (string)
email (string, unique)
date_of_birth (date, nullable)
gender (enum: male, female)
address (text, nullable)
qualification (string, nullable)
specialization (string, nullable)
joining_date (date)
status (enum: active, inactive, on_leave, resigned, default: active)
created_at, updated_at
```

**Relationships:**
- `belongsTo(User)`
- `belongsToMany(Classes)` via `class_teacher` pivot
- `belongsToMany(Subjects)` via `subject_teacher` pivot

---

#### classes
Academic classes/grades.

```
id (PK)
name (string) - e.g., "Grade 1", "Grade 2"
name_ar (string) - Arabic name
numeric_level (integer) - 1, 2, 3... for sorting
capacity (integer, nullable)
is_active (boolean, default: true)
created_at, updated_at
```

**Relationships:**
- `hasMany(Sections)`
- `hasMany(Students)`
- `belongsToMany(Teachers)` via `class_teacher` pivot
- `belongsToMany(Subjects)` via `class_subject` pivot

---

#### sections
Divisions within a class (e.g., Grade 1-A, Grade 1-B).

```
id (PK)
class_id (FK: classes.id)
name (string) - "A", "B", "C"
capacity (integer, nullable)
room_number (string, nullable)
is_active (boolean, default: true)
created_at, updated_at
```

**Relationships:**
- `belongsTo(Class)`
- `hasMany(Students)`

---

#### subjects
Academic subjects.

```
id (PK)
name (string)
name_ar (string)
code (string, unique, nullable) - e.g., "MATH101"
description (text, nullable)
is_active (boolean, default: true)
created_at, updated_at
```

**Relationships:**
- `belongsToMany(Classes)` via `class_subject` pivot
- `belongsToMany(Teachers)` via `subject_teacher` pivot

---

#### attendance_records
Daily attendance tracking.

```
id (PK)
student_id (FK: students.id)
class_id (FK: classes.id)
section_id (FK: sections.id, nullable)
date (date)
status (enum: present, absent, late, excused, default: present)
marked_by (FK: users.id) - teacher/admin who marked
notes (text, nullable)
created_at, updated_at

UNIQUE KEY: student_id + date
```

**Relationships:**
- `belongsTo(Student)`
- `belongsTo(Class)`
- `belongsTo(Section)`
- `belongsTo(User, 'marked_by')`

---

### 3.2 Pivot Tables

#### class_teacher
```
id (PK)
class_id (FK: classes.id)
teacher_id (FK: teachers.id)
is_class_teacher (boolean, default: false) - primary class teacher
created_at, updated_at
```

#### subject_teacher
```
id (PK)
subject_id (FK: subjects.id)
teacher_id (FK: teachers.id)
class_id (FK: classes.id, nullable) - if subject is taught to specific class
created_at, updated_at
```

#### class_subject
```
id (PK)
class_id (FK: classes.id)
subject_id (FK: subjects.id)
created_at, updated_at
```

---

## 4. Application Structure

### 4.1 Directory Organization

```
app/
├── Http/
│   ├── Controllers/
│   │   ├── Auth/
│   │   │   ├── LoginController.php
│   │   │   └── RegisterController.php
│   │   ├── Admin/
│   │   │   ├── DashboardController.php
│   │   │   ├── StudentController.php
│   │   │   ├── TeacherController.php
│   │   │   ├── ClassController.php
│   │   │   ├── SubjectController.php
│   │   │   ├── AttendanceController.php
│   │   │   └── TokenController.php
│   │   ├── Public/
│   │   │   └── EnrollmentController.php
│   │   └── LocaleController.php
│   ├── Middleware/
│   │   ├── RoleMiddleware.php
│   │   ├── SetLocale.php
│   │   └── CheckTokenValid.php
│   └── Requests/
│       ├── StoreStudentRequest.php
│       ├── EnrollmentFormRequest.php
│       └── StoreTeacherRequest.php
├── Models/
│   ├── User.php
│   ├── Student.php
│   ├── Teacher.php
│   ├── Parent.php
│   ├── ClassModel.php
│   ├── Section.php
│   ├── Subject.php
│   ├── RegistrationToken.php
│   └── AttendanceRecord.php
├── Services/
│   ├── AdmissionNumberService.php
│   ├── TokenService.php
│   └── AttendanceService.php
└── View/
    └── Components/
        ├── Layout/
        │   ├── AppLayout.php
        │   ├── Sidebar.php
        │   ├── Topbar.php
        │   └── Footer.php
        ├── Card.php
        ├── StatCard.php
        ├── Modal.php
        ├── Alert.php
        └── LanguageSwitcher.php

resources/
├── views/
│   ├── components/
│   │   ├── layout/
│   │   │   ├── app.blade.php
│   │   │   ├── guest.blade.php
│   │   │   ├── sidebar.blade.php
│   │   │   ├── topbar.blade.php
│   │   │   └── footer.blade.php
│   │   ├── card.blade.php
│   │   ├── stat-card.blade.php
│   │   ├── modal.blade.php
│   │   ├── alert.blade.php
│   │   └── language-switcher.blade.php
│   ├── admin/
│   │   ├── dashboard.blade.php
│   │   ├── students/
│   │   │   ├── index.blade.php
│   │   │   ├── create.blade.php
│   │   │   ├── edit.blade.php
│   │   │   └── show.blade.php
│   │   ├── teachers/
│   │   ├── classes/
│   │   ├── subjects/
│   │   ├── attendance/
│   │   └── tokens/
│   │       ├── index.blade.php
│   │       ├── create.blade.php
│   │       └── show.blade.php
│   ├── public/
│   │   ├── enrollment/
│   │   │   ├── form.blade.php
│   │   │   ├── success.blade.php
│   │   │   └── error.blade.php
│   └── auth/
│       ├── login.blade.php
│       └── register.blade.php
└── lang/
    ├── en/
    │   ├── dashboard.php
    │   ├── students.php
    │   ├── teachers.php
    │   ├── classes.php
    │   ├── subjects.php
    │   ├── attendance.php
    │   ├── tokens.php
    │   ├── enrollment.php
    │   ├── auth.php
    │   └── common.php
    └── ar/
        └── (same structure as en/)

routes/
├── web.php
├── admin.php (optional grouping)
└── public.php (optional grouping)

database/
├── migrations/
├── seeders/
│   ├── DatabaseSeeder.php
│   ├── UserSeeder.php
│   ├── ClassSeeder.php
│   └── SubjectSeeder.php
└── factories/
```

---

### 4.2 Route Structure

#### Web Routes (`routes/web.php`)

```
// Public Routes
GET  /                                  -> Public landing page
GET  /enroll/{token}                    -> Public enrollment form (token protected)
POST /enroll/{token}                    -> Submit enrollment form
GET  /enroll/success/{student}          -> Enrollment success page

// Authentication
GET  /login                             -> Login form
POST /login                             -> Handle login
POST /logout                            -> Handle logout

// Language Switcher
POST /locale/{locale}                   -> Switch language (en/ar)

// Admin Routes (protected: auth, role:admin)
Prefix: /admin

GET  /admin/dashboard                   -> Admin dashboard

// Student Management
GET  /admin/students                    -> List students (with filters)
GET  /admin/students/create             -> Create student form (admin direct)
POST /admin/students                    -> Store student
GET  /admin/students/{student}          -> View student profile
GET  /admin/students/{student}/edit     -> Edit student
PUT  /admin/students/{student}          -> Update student
DELETE /admin/students/{student}        -> Soft delete student

// Token Management
GET  /admin/tokens                      -> List all tokens (with filters)
GET  /admin/tokens/create               -> Generate token(s) form
POST /admin/tokens                      -> Generate token(s)
GET  /admin/tokens/{token}              -> View token details
POST /admin/tokens/{token}/disable      -> Disable a token

// Teacher Management
GET  /admin/teachers                    -> List teachers
GET  /admin/teachers/create             -> Create teacher
POST /admin/teachers                    -> Store teacher
GET  /admin/teachers/{teacher}          -> View teacher profile
GET  /admin/teachers/{teacher}/edit     -> Edit teacher
PUT  /admin/teachers/{teacher}          -> Update teacher

// Class Management
GET  /admin/classes                     -> List classes
GET  /admin/classes/create              -> Create class
POST /admin/classes                     -> Store class
GET  /admin/classes/{class}/edit        -> Edit class
PUT  /admin/classes/{class}             -> Update class

// Section Management
GET  /admin/classes/{class}/sections    -> Manage sections for a class
POST /admin/classes/{class}/sections    -> Create section

// Subject Management
GET  /admin/subjects                    -> List subjects
GET  /admin/subjects/create             -> Create subject
POST /admin/subjects                    -> Store subject
GET  /admin/subjects/{subject}/edit     -> Edit subject
PUT  /admin/subjects/{subject}          -> Update subject

// Attendance
GET  /admin/attendance                  -> Attendance dashboard
GET  /admin/attendance/mark             -> Mark attendance form (select class/date)
POST /admin/attendance/mark             -> Save attendance
GET  /admin/attendance/report           -> View attendance reports
```

---

### 4.3 Controller Responsibilities

#### Admin/DashboardController
- `index()`: Show admin dashboard with stats
  - Total students, teachers, classes
  - Recent enrollments
  - Attendance summary
  - Recent token usage
  - Charts (students by class, monthly admissions)

#### Admin/StudentController
- `index()`: List students with filters (class, status, session, search)
- `create()`: Show form to create student directly (no token)
- `store()`: Validate and create student, generate admission number
- `show($id)`: Display full student profile
- `edit($id)`: Show edit form
- `update($id)`: Update student details
- `destroy($id)`: Soft delete student

#### Admin/TokenController
- `index()`: List all tokens with filters (status, date range)
- `create()`: Show token generation form
- `store()`: Generate one or multiple tokens with unique codes
- `show($id)`: View token details (who created, when, usage status, linked student)
- `disable($id)`: Mark token as disabled

#### Public/EnrollmentController
- `show($token)`:
  - Validate token (unused, not expired, not disabled)
  - If invalid: show error page
  - If valid: show multi-section enrollment form
- `store($token)`:
  - Validate all form fields
  - Create student record
  - Generate admission number
  - Mark token as used
  - Redirect to success page with admission number

#### LocaleController
- `switch($locale)`: Set session locale to 'en' or 'ar', redirect back

---

## 5. Module Specifications

### 5.1 Dashboard Module

#### Purpose
Provide at-a-glance overview of school metrics and quick access to main features.

#### Components
1. **Stat Cards** (top row)
   - Total Students (with icon, trend)
   - Total Teachers
   - Total Classes
   - Attendance Rate (today or this week)

2. **Quick Actions Panel**
   - Buttons: Add Student, Generate Token, Mark Attendance, View Reports

3. **Recent Activity Feed**
   - Recent enrollments (last 5)
   - Recent token usage
   - Recent attendance entries

4. **Charts Section**
   - Bar chart: Students per class
   - Line chart: Monthly admissions trend
   - Pie chart: Student status distribution

5. **Announcements/Notices** (optional)
   - School notices or upcoming events

#### UI/UX
- Clean grid layout: 4 stat cards in a row, responsive to 2 on tablet, 1 on mobile
- Cards have subtle shadow, hover effect
- Charts use Chart.js loaded via CDN
- Color scheme: Blues and greens, professional palette

---

### 5.2 Student Management Module

#### Admin Perspective

**a) Student List (`/admin/students`)**

**Purpose**: Browse, search, filter all students.

**Features**:
- **Data Table**:
  - Columns: Admission Number, Full Name, Class, Section, Status, Actions
  - Pagination (15 per page)
  - Sort by: Name, Admission Number, Date
- **Filters** (top bar):
  - Class dropdown
  - Status dropdown (Active, Inactive, etc.)
  - Session year dropdown
  - Search bar (by name or admission number)
- **Actions per row**:
  - View (eye icon)
  - Edit (pencil icon)
  - Delete (trash icon with confirmation modal)
- **Bulk Actions** (optional):
  - Export to CSV
  - Bulk status update

**UI/UX**:
- Table with striped rows, hover highlight
- Filter bar uses Alpine.js for dynamic dropdowns without reload
- Responsive: on mobile, show cards instead of table

---

**b) Create Student (`/admin/students/create`)**

**Purpose**: Admin can create a student directly without a token.

**Form Sections**:
1. **Personal Information**
   - First Name, Middle Name, Last Name
   - Gender (radio buttons)
   - Date of Birth (date picker)
   - Place of Birth, Nationality, Religion, Blood Group

2. **Contact Information**
   - Phone, Email
   - Current Address (textarea)
   - Permanent Address (textarea)

3. **Academic Information**
   - Class (dropdown)
   - Section (dropdown, filtered by selected class)
   - Session Year (input or dropdown, e.g., 2024-2025)
   - Admission Date (date picker, defaults to today)

4. **Previous School (optional)**
   - School Name, Address
   - Last Class/Grade
   - Reason for Transfer (textarea)

5. **Health & Allergy Information**
   - Has Allergy? (yes/no toggle)
   - If yes: Allergy Type, Severity (dropdown), Notes (textarea)
   - General Health Notes (textarea)

6. **Parent/Guardian Details**
   - Add one or more parents (repeatable fields or separate section)
   - For each parent:
     - Name, Relationship, Phone, Email, Address
     - Is Primary Contact? (checkbox)
     - Is Emergency Contact? (checkbox)

**Behavior**:
- On submit:
  - Validate all fields
  - Create `Student` record
  - Call `AdmissionNumberService::generate($student)` to assign admission number
  - Create `Parent` records and link via pivot
  - Redirect to student profile with success message

**UI/UX**:
- Multi-section form with clear headings
- Conditional fields (allergy details show only if "Has Allergy" = yes) using Alpine.js
- Inline validation feedback
- "Cancel" and "Save Student" buttons at bottom

---

**c) Student Profile (`/admin/students/{id}`)**

**Purpose**: View complete student details.

**Layout**:
- **Header Section**:
  - Student photo (placeholder or uploaded)
  - Full Name
  - Admission Number (prominent)
  - Status badge (Active, Inactive, etc.)
  - Quick actions: Edit, Print Profile, Delete

- **Tabbed Content** (using Alpine.js):
  - **Personal Info Tab**:
    - All personal details in labeled sections
  - **Academic Info Tab**:
    - Current class, section, session
    - Admission details
    - Previous school info
  - **Parent/Guardian Tab**:
    - List of linked parents with contact details
  - **Health Info Tab**:
    - Allergy and health notes
  - **Attendance Tab** (optional):
    - Attendance summary, recent records
  - **Activity Log Tab** (optional):
    - History of changes (created, updated, etc.)

**UI/UX**:
- Clean, card-based layout
- Tabs with smooth transitions
- Print-friendly version (CSS @media print)

---

**d) Edit Student (`/admin/students/{id}/edit`)**

**Purpose**: Update student details.

**Form**: Same structure as Create Student, but pre-filled with existing data.

**Behavior**:
- On submit: validate, update record, redirect to profile with success message

---

### 5.3 Registration Token System Module

#### Purpose
Control and track public enrollments via unique, one-time-use tokens.

#### Admin Features

**a) Token List (`/admin/tokens`)**

**Features**:
- **Data Table**:
  - Columns: Token Code, Status, Generated By, Generated At, Used At, Used By (Student), Actions
  - Pagination
- **Filters**:
  - Status (unused, used, disabled, expired)
  - Date range (generated date)
  - Search by token code
- **Actions per row**:
  - View Details
  - Disable (if unused)
- **Bulk Generate Button** (top right)

**UI/UX**:
- Color-coded status badges (green = unused, blue = used, red = disabled, gray = expired)
- Clean table, hover effects

---

**b) Generate Tokens (`/admin/tokens/create`)**

**Form**:
- **Number of Tokens to Generate**: Input (default 1, max 100)
- **Expiry Date** (optional): Date picker
- **Notes**: Textarea for admin reference

**Behavior**:
- On submit:
  - Generate X tokens with unique codes (e.g., `ENROLL-2024-ABCD1234`)
  - Store in `registration_tokens` table
  - Show success message with list of generated tokens
  - Provide "Download as PDF/CSV" button to share tokens with parents

**Token Code Format**:
```
ENROLL-{YEAR}-{RANDOM8}
Example: ENROLL-2024-A7F3B9C2
```

**UI/UX**:
- Simple form, clear instructions
- Success page shows generated tokens in a table/list
- Option to copy codes to clipboard (Alpine.js + vanilla JS)

---

**c) Token Details (`/admin/tokens/{id}`)**

**Display**:
- Token Code (large, prominent)
- Status badge
- Generated By (admin name)
- Generated At (date/time)
- Expires At (if set)
- Used At (if used)
- Used By Student: Link to student profile (if used)
- Public Enrollment URL: `https://yourschool.com/enroll/{token_code}`
  - Copy link button
- Notes (admin notes)
- Actions:
  - Disable Token (if unused)
  - View Student (if used)

**UI/UX**:
- Card layout with sections
- Clear visual indication of status

---

### 5.4 Public Enrollment Form Module

#### Purpose
Allow parents to self-enroll their children using a valid token.

#### Access Flow

**URL**: `/enroll/{token_code}`

**Step 1: Token Validation**

When a parent visits `/enroll/ENROLL-2024-ABCD1234`:
1. System checks token in database
2. **Validation Rules**:
   - Token exists
   - Status = `unused`
   - Not expired (if `expires_at` is set)
   - Not disabled

**If Invalid**:
- Show error page (`resources/views/public/enrollment/error.blade.php`)
- Message examples:
  - "This token is invalid or does not exist."
  - "This token has already been used."
  - "This token has been disabled."
  - "This token has expired."
- Provide contact information for school admin

**If Valid**:
- Show enrollment form (`resources/views/public/enrollment/form.blade.php`)

---

**Step 2: Enrollment Form**

**Layout & Sections**:

This is a **public-facing, mobile-friendly, smart form**. It should feel professional and welcoming.

**Header**:
- School logo and name
- Title: "Student Enrollment Form"
- Subtitle: "Please fill in all required information accurately."

**Form Sections** (using accordions or vertical sections):

1. **Student Information**
   - First Name* (required)
   - Middle Name
   - Last Name* (required)
   - Gender* (radio: Male/Female)
   - Date of Birth* (date picker)
   - Place of Birth
   - Nationality
   - Religion
   - Blood Group

2. **Contact Information**
   - Phone Number
   - Email Address
   - Current Address* (textarea)
   - Permanent Address (textarea, with "Same as current" checkbox)

3. **Academic Information**
   - Class/Grade Applying For* (dropdown)
   - Session Year* (auto-filled or dropdown, e.g., 2024-2025)

4. **Previous School Information** (optional section)
   - "Has the student attended another school?" (Yes/No toggle)
   - If Yes:
     - Previous School Name
     - Previous School Address
     - Last Class/Grade Attended
     - Reason for Transfer (textarea)

5. **Health & Allergy Information**
   - "Does the student have any allergies?" (Yes/No toggle)
   - If Yes:
     - Allergy Type (e.g., Food, Medicine, Environmental)
     - Severity (Mild/Moderate/Severe dropdown)
     - Detailed Notes (textarea)
   - General Health Notes (textarea, optional)

6. **Parent/Guardian Information**
   - Section for **Primary Parent/Guardian**:
     - First Name*, Last Name*
     - Relationship* (Father/Mother/Guardian/Other)
     - Phone Number* (Primary)
     - Phone Number (Secondary)
     - Email Address
     - Occupation
     - National ID
     - Address (with "Same as student" checkbox)
     - Is Emergency Contact? (checkbox, default: yes)

   - Optional: **Add Second Parent/Guardian** (button to expand form for second parent)

7. **Declaration & Consent**
   - Checkbox: "I declare that all information provided is accurate and complete."*
   - Checkbox: "I agree to the school's terms and conditions."*

**Submit Button**: "Submit Enrollment"

---

**Behavior on Submit**:

1. **Frontend Validation**:
   - All required fields filled
   - Valid email format
   - Valid date format
   - At least one parent/guardian

2. **Backend Validation** (`EnrollmentFormRequest`):
   - Re-validate all fields
   - Check token is still valid and unused

3. **Create Student Record**:
   - Create `Student` with all submitted data
   - Link to `registration_token_id`

4. **Generate Admission Number**:
   - Call `AdmissionNumberService::generate($student)`
   - Assign to student

5. **Create Parent Records**:
   - Create `Parent` record(s)
   - Link to student via `parent_student` pivot

6. **Mark Token as Used**:
   - Update token: `status = 'used'`, `used_at = now()`, `used_by_student_id = $student->id`

7. **Redirect to Success Page**:
   - `/enroll/success/{student_id}` or `/enroll/success/{admission_number}`

---

**Step 3: Success Page**

**Display**:
- Success icon (checkmark)
- Congratulations message
- **Admission Number** displayed prominently
- Student Name
- Class Applied For
- Message: "Your enrollment has been successfully submitted. Please save your admission number for future reference."
- Next Steps:
  - "You will be contacted by the school administration shortly."
  - "Please bring the following documents on your first visit: ..."
- Print Button (to print confirmation)
- Download PDF Button (optional)

**UI/UX**:
- Celebratory design (green colors, success theme)
- Clear, large admission number
- Print-friendly CSS

---

#### UI/UX for Enrollment Form

**Design Principles**:
- **Clean & Spacious**: Ample whitespace, not cluttered
- **Mobile-First**: Works beautifully on phone screens
- **Progressive Disclosure**: Show/hide conditional fields (allergy details, previous school) using Alpine.js
- **Inline Validation**: Real-time feedback (green checkmark for valid, red message for invalid)
- **Section Headers**: Clear visual separation between sections
- **Help Text**: Small gray text under fields to guide users
- **Loading State**: Show spinner on submit button while processing
- **Error Handling**: If submission fails, show clear error message without losing form data

**Example Conditional Field (Alpine.js)**:
```html
<div x-data="{ hasAllergy: false }">
  <label>Does the student have any allergies?</label>
  <input type="radio" name="has_allergy" value="1" x-model="hasAllergy" :value="true"> Yes
  <input type="radio" name="has_allergy" value="0" x-model="hasAllergy" :value="false"> No

  <div x-show="hasAllergy" x-transition>
    <!-- Allergy detail fields appear here -->
  </div>
</div>
```

---

### 5.5 Admission Number Generation System

#### Purpose
Automatically generate a unique, informative admission number for every student.

#### Logic Specification

**Format Structure**:
```
{YEAR}{MONTH}-{POSITION_IN_MONTH}-{OVERALL_POSITION}

Components:
- YEAR: 4-digit admission year (e.g., 2024)
- MONTH: 2-digit admission month (e.g., 01, 02, ..., 12)
- POSITION_IN_MONTH: 2-digit, sequential count of admissions in that year-month (01, 02, 03, ...)
- OVERALL_POSITION: Auto-incrementing count across all time (e.g., 00001, 00002, ...)

Example: 202401-01-00123
Meaning: Admitted in January 2024, 1st admission that month, 123rd overall admission in school history.
```

#### Implementation Service

**Class**: `App\Services\AdmissionNumberService`

**Method**: `generate(Student $student): string`

**Algorithm**:
1. Extract `admission_year` and `admission_month` from `$student->admission_date`
2. **Find Position in Month**:
   - Query: Count students with same `admission_year` and `admission_month` (excluding current student if updating)
   - `$positionInMonth = $count + 1`
3. **Find Overall Position**:
   - Query: Max `admission_position_overall` from all students
   - `$overallPosition = $max + 1`
4. **Build Admission Number**:
   ```
   $year = $student->admission_year; // 2024
   $month = str_pad($student->admission_month, 2, '0', STR_PAD_LEFT); // 01
   $posInMonth = str_pad($positionInMonth, 2, '0', STR_PAD_LEFT); // 01
   $overall = str_pad($overallPosition, 5, '0', STR_PAD_LEFT); // 00123

   $admissionNumber = "{$year}{$month}-{$posInMonth}-{$overall}";
   // Result: 202401-01-00123
   ```
5. **Save to Student**:
   ```
   $student->admission_number = $admissionNumber;
   $student->admission_position_in_month = $positionInMonth;
   $student->admission_position_overall = $overallPosition;
   $student->save();
   ```
6. **Return** `$admissionNumber`

**Uniqueness**:
- Admission number is unique (database unique constraint)
- Overall position auto-increments, ensuring no duplicates

**When to Call**:
- Immediately after creating a student record (in controller's `store()` method)
- Before displaying success message or profile

---

### 5.6 Teacher Management Module (High-Level)

#### Purpose
Manage teacher records and assignments.

#### Features
- **List Teachers**: Table with name, employee ID, email, subjects taught, status
- **Create Teacher**: Form with personal info, qualification, subjects, classes assigned
- **View Teacher Profile**: Display all details, assigned classes/subjects, schedule
- **Edit Teacher**: Update details
- **Assign to Classes/Subjects**: Manage relationships

#### UI/UX
- Similar to student management (table, filters, forms)
- Assignment interface: multi-select dropdowns or drag-and-drop lists

---

### 5.7 Class & Section Management Module (High-Level)

#### Purpose
Define academic structure (grades/classes and their sections).

#### Features
- **List Classes**: Table with class name, numeric level, capacity, sections count
- **Create Class**: Form with name (EN/AR), numeric level, capacity
- **Manage Sections**: For each class, create sections (A, B, C), set room number, capacity
- **View Class Details**: List of students in class, assigned teachers, subjects

#### UI/UX
- Hierarchical view: Class > Sections
- Inline section creation (add section button in class details view)

---

### 5.8 Subject Management Module (High-Level)

#### Purpose
Define subjects taught in school.

#### Features
- **List Subjects**: Table with subject name (EN/AR), code, assigned classes
- **Create Subject**: Form with name, code, description
- **Assign to Classes**: Link subjects to classes (a subject may be taught in multiple classes)

#### UI/UX
- Simple table, inline editing (optional)
- Assignment: checkbox list of classes

---

### 5.9 Attendance Module (High-Level)

#### Purpose
Mark and track daily attendance for students.

#### Features

**a) Mark Attendance (`/admin/attendance/mark`)**
- **Select Class** and **Date**
- Display list of students in that class
- For each student: Present / Absent / Late / Excused (radio buttons or quick toggles)
- **Save Attendance** button
- On save: Create/update `attendance_records` for each student for that date

**b) Attendance Reports (`/admin/attendance/report`)**
- Filter by: Class, Date Range, Student
- Display attendance summary:
  - Per student: total present, absent, late, percentage
  - Per class: average attendance rate
- Export to CSV/PDF

#### UI/UX
- Quick-toggle interface (click to change status, color-coded)
- Calendar view for selecting date
- Visual summary (charts showing attendance trends)

---

## 6. User Flows & Journeys

### 6.1 Admin Creates Student Directly

1. Admin logs in → Dashboard
2. Clicks "Students" in sidebar → Student list
3. Clicks "Add New Student" button → Create student form
4. Fills in all sections (personal, academic, parents, health)
5. Clicks "Save Student"
6. System validates, creates student, generates admission number
7. Redirects to student profile with success message: "Student created successfully. Admission Number: 202401-01-00123"

---

### 6.2 Admin Generates and Shares Enrollment Tokens

1. Admin logs in → Dashboard
2. Clicks "Tokens" in sidebar → Token list
3. Clicks "Generate Tokens" button → Token generation form
4. Enters: Number of tokens = 10, Expiry date = 30 days from now
5. Clicks "Generate"
6. System creates 10 tokens, shows success page with token codes
7. Admin clicks "Download as PDF" → Gets printable token list
8. Admin distributes tokens to prospective parents (via email, print, etc.)

---

### 6.3 Parent Enrolls Child Using Token

1. Parent receives token: `ENROLL-2024-A7F3B9C2`
2. Visits: `https://school.com/enroll/ENROLL-2024-A7F3B9C2`
3. System validates token → Valid, shows enrollment form
4. Parent fills in:
   - Student details (name, DOB, gender, etc.)
   - Contact info
   - Academic info (class applying for)
   - Previous school (if applicable)
   - Allergy info (selects "Yes", fills in details)
   - Parent info (father and mother details)
5. Checks declaration checkboxes
6. Clicks "Submit Enrollment"
7. System validates:
   - All fields correct
   - Token still valid
8. Creates student, generates admission number: `202403-05-00456`
9. Marks token as used
10. Redirects to success page showing:
    - "Enrollment successful!"
    - Admission Number: 202403-05-00456
    - Student Name
    - Next steps
11. Parent prints/saves admission number

---

### 6.4 Admin Reviews Enrollment Created via Token

1. Admin logs in → Dashboard
2. Sees "Recent Enrollments" widget showing new student: "John Doe - 202403-05-00456 (via token)"
3. Clicks on student name → Student profile
4. Reviews all submitted information
5. Can edit if needed (e.g., assign to specific section)
6. Clicks "Tokens" → Token list
7. Sees token `ENROLL-2024-A7F3B9C2` marked as "Used" with link to student profile

---

### 6.5 Admin Disables an Unused Token

1. Admin logs in → Tokens list
2. Filters by "Unused" status
3. Finds a token that should no longer be used (e.g., parent declined admission)
4. Clicks "View" → Token details page
5. Clicks "Disable Token" button
6. Confirmation modal: "Are you sure?"
7. Clicks "Yes, Disable"
8. Token status changes to "Disabled"
9. If anyone tries to use that token URL, they see error: "This token has been disabled."

---

### 6.6 Teacher Marks Attendance

1. Teacher logs in → Dashboard
2. Clicks "Attendance" → Mark Attendance
3. Selects Class: "Grade 3", Section: "A", Date: Today
4. System displays list of students in Grade 3-A
5. For each student, teacher clicks status: Present/Absent/Late/Excused
6. Clicks "Save Attendance"
7. System creates attendance records
8. Success message: "Attendance marked for 25 students."
9. Teacher can view report to see summary

---

## 7. UI/UX Design System

### 7.1 Design Principles

- **Clarity**: Every element has a clear purpose
- **Consistency**: Same patterns across all pages (buttons, forms, cards)
- **Responsiveness**: Mobile-first, scales beautifully to desktop
- **Accessibility**: High contrast, clear labels, keyboard navigation
- **Modern & Clean**: Inspired by React dashboards (Material UI, Ant Design)
- **Professional**: Suitable for educational institution

---

### 7.2 Layout Structure

#### Authenticated Layout (Admin/Teacher)

**Components**:
1. **Sidebar** (left, fixed on desktop, slide-out on mobile)
   - School logo/name at top
   - Navigation menu (icons + labels)
     - Dashboard
     - Students
     - Teachers
     - Classes
     - Subjects
     - Attendance
     - Tokens
     - Reports
     - Settings
   - User info at bottom (name, role, logout link)

2. **Topbar** (top, fixed)
   - Page title (dynamic based on current page)
   - Right side:
     - Language switcher (EN/AR dropdown)
     - Notifications icon (badge if new)
     - User avatar dropdown (profile, settings, logout)
   - Hamburger menu (mobile, to toggle sidebar)

3. **Main Content Area**
   - Breadcrumbs (Home > Students > Create)
   - Page-specific content
   - Footer (optional, copyright, version)

**CSS Framework**:
- Tailwind CSS (CDN) for utility classes
- Custom CSS (minimal) for specific styles

**Alpine.js Usage**:
- Sidebar toggle (mobile)
- Dropdown menus (language, user menu)
- Modal dialogs
- Tab switching
- Conditional field visibility

---

#### Guest Layout (Public Enrollment)

**Components**:
1. **Header**
   - School logo and name
   - Language switcher
2. **Main Content**
   - Enrollment form or error/success messages
3. **Footer**
   - Contact information
   - Copyright

**Design**:
- Centered, card-based layout
- Clean, minimal, welcoming

---

### 7.3 Component Library

#### a) Stat Card
**Purpose**: Display key metrics on dashboard.

**Structure**:
- Icon (top-left, colored background)
- Value (large, bold)
- Label (small, gray)
- Trend indicator (optional: +5% this month, green/red)

**Example**:
```
┌─────────────────────┐
│ 👨‍🎓  (icon)         │
│ 1,234  (value)      │
│ Total Students      │
│ +12% ↑ (trend)      │
└─────────────────────┘
```

**Blade Component**: `<x-stat-card icon="..." value="..." label="..." trend="..." />`

---

#### b) Card
**Purpose**: General container for content sections.

**Structure**:
- Header (optional, with title and actions)
- Body (content)
- Footer (optional, actions)

**Blade Component**: `<x-card title="...">...</x-card>`

---

#### c) Modal
**Purpose**: Overlay dialogs for confirmations, forms.

**Behavior** (Alpine.js):
- Triggered by button click
- Backdrop (semi-transparent, dark)
- Content box (centered, white)
- Close button (X icon)
- Actions (Cancel, Confirm)

**Blade Component**: `<x-modal id="..." title="...">...</x-modal>`

---

#### d) Alert
**Purpose**: Display success, error, warning, info messages.

**Types**:
- Success (green, checkmark icon)
- Error (red, X icon)
- Warning (yellow, exclamation icon)
- Info (blue, info icon)

**Blade Component**: `<x-alert type="success" message="..." />`

---

#### e) Language Switcher
**Purpose**: Toggle between English and Arabic.

**UI**:
- Dropdown or toggle buttons (EN | AR)
- Current language highlighted
- On click, POST to `/locale/{locale}`, set session, redirect back

**Blade Component**: `<x-language-switcher />`

---

#### f) Button
**Purpose**: Standard, consistent buttons.

**Variants**:
- Primary (blue, for main actions)
- Secondary (gray, for cancel/back)
- Danger (red, for delete)
- Success (green, for save/submit)

**Blade Component**: `<x-button variant="primary">Save</x-button>`

---

### 7.4 Color Palette

**Primary Colors**:
- Primary: #3B82F6 (blue)
- Success: #10B981 (green)
- Warning: #F59E0B (amber)
- Danger: #EF4444 (red)
- Info: #6366F1 (indigo)

**Neutral Colors**:
- Gray 50-900 (Tailwind defaults)
- Text: Gray 900 (dark) on light backgrounds
- Backgrounds: White, Gray 50, Gray 100

**Always LTR**:
- Text direction remains left-to-right even in Arabic
- Only text content changes, not layout direction

---

### 7.5 Typography

**Font Stack**:
- Sans-serif: Use Tailwind's default or Google Fonts (Inter, Roboto) via CDN
- Arabic-friendly: Ensure font supports Arabic characters (Noto Sans Arabic, Tajawal)

**Hierarchy**:
- H1: Page titles (text-3xl, font-bold)
- H2: Section headers (text-2xl, font-semibold)
- H3: Subsection headers (text-xl, font-medium)
- Body: text-base, regular
- Small: text-sm (for labels, help text)

---

### 7.6 Iconography

**Icon Library**: Font Awesome (CDN) or Heroicons (inline SVG)

**Usage**:
- Sidebar menu: icon + label
- Buttons: optional icon (e.g., "Save" button with checkmark)
- Stat cards: large icon in colored circle
- Status badges: icon (checkmark for success, X for error)

---

### 7.7 Responsiveness

**Breakpoints** (Tailwind defaults):
- sm: 640px
- md: 768px
- lg: 1024px
- xl: 1280px

**Responsive Behavior**:
- **Mobile (<768px)**:
  - Sidebar hidden by default, slide-out menu
  - Stat cards stacked (1 per row)
  - Tables convert to card view or horizontal scroll
  - Forms: single column
- **Tablet (768px - 1024px)**:
  - Sidebar visible or collapsible
  - Stat cards: 2 per row
  - Forms: single or two columns
- **Desktop (>1024px)**:
  - Sidebar always visible
  - Stat cards: 4 per row
  - Forms: two or three columns
  - Full-width tables

---

### 7.8 Interactivity (Alpine.js)

**Common Patterns**:

1. **Dropdown Menu**
```html
<div x-data="{ open: false }">
  <button @click="open = !open">Menu</button>
  <div x-show="open" @click.away="open = false">
    <!-- Menu items -->
  </div>
</div>
```

2. **Modal Dialog**
```html
<div x-data="{ showModal: false }">
  <button @click="showModal = true">Open Modal</button>
  <div x-show="showModal" x-transition>
    <!-- Modal content -->
    <button @click="showModal = false">Close</button>
  </div>
</div>
```

3. **Conditional Fields**
```html
<div x-data="{ hasAllergy: false }">
  <input type="checkbox" x-model="hasAllergy"> Has Allergy
  <div x-show="hasAllergy" x-transition>
    <!-- Allergy details fields -->
  </div>
</div>
```

4. **Tabs**
```html
<div x-data="{ tab: 'personal' }">
  <button @click="tab = 'personal'" :class="{'active': tab === 'personal'}">Personal</button>
  <button @click="tab = 'academic'" :class="{'active': tab === 'academic'}">Academic</button>

  <div x-show="tab === 'personal'">Personal Info...</div>
  <div x-show="tab === 'academic'">Academic Info...</div>
</div>
```

---

## 8. Localization Strategy

### 8.1 Language Files Structure

**Directory**: `resources/lang/`

**Languages**:
- `en/` (English)
- `ar/` (Arabic)

**Files** (same structure in both):
- `common.php` - Shared terms (save, cancel, delete, edit, view, etc.)
- `auth.php` - Login, logout, register, etc.
- `dashboard.php` - Dashboard labels and messages
- `students.php` - Student module terms
- `teachers.php` - Teacher module terms
- `classes.php` - Class/section terms
- `subjects.php` - Subject terms
- `attendance.php` - Attendance terms
- `tokens.php` - Token system terms
- `enrollment.php` - Public enrollment form terms
- `validation.php` - Custom validation messages

---

### 8.2 Translation Key Examples

**`resources/lang/en/dashboard.php`**
```php
return [
    'title' => 'Dashboard',
    'total_students' => 'Total Students',
    'total_teachers' => 'Total Teachers',
    'total_classes' => 'Total Classes',
    'attendance_rate' => 'Attendance Rate',
    'recent_enrollments' => 'Recent Enrollments',
    'quick_actions' => 'Quick Actions',
    'add_student' => 'Add Student',
    'generate_token' => 'Generate Token',
    'mark_attendance' => 'Mark Attendance',
];
```

**`resources/lang/ar/dashboard.php`**
```php
return [
    'title' => 'لوحة التحكم',
    'total_students' => 'إجمالي الطلاب',
    'total_teachers' => 'إجمالي المعلمين',
    'total_classes' => 'إجمالي الفصول',
    'attendance_rate' => 'معدل الحضور',
    'recent_enrollments' => 'التسجيلات الأخيرة',
    'quick_actions' => 'إجراءات سريعة',
    'add_student' => 'إضافة طالب',
    'generate_token' => 'إنشاء رمز',
    'mark_attendance' => 'تسجيل الحضور',
];
```

**Usage in Blade**:
```blade
<h1>{{ __('dashboard.title') }}</h1>
<x-stat-card label="{{ __('dashboard.total_students') }}" value="1234" />
```

---

### 8.3 Language Switcher Implementation

**Route** (`routes/web.php`):
```php
POST /locale/{locale}  -> LocaleController@switch
```

**Controller** (`App/Http/Controllers/LocaleController.php`):
```php
public function switch($locale)
{
    if (in_array($locale, ['en', 'ar'])) {
        session(['locale' => $locale]);
    }
    return redirect()->back();
}
```

**Middleware** (`App/Http/Middleware/SetLocale.php`):
```php
public function handle($request, Closure $next)
{
    $locale = session('locale', config('app.locale'));
    app()->setLocale($locale);
    return $next($request);
}
```

**Blade Component** (`resources/views/components/language-switcher.blade.php`):
```blade
<div x-data="{ open: false }">
    <button @click="open = !open">
        {{ strtoupper(app()->getLocale()) }} ▼
    </button>
    <div x-show="open" @click.away="open = false">
        <form method="POST" action="/locale/en">
            @csrf
            <button>English</button>
        </form>
        <form method="POST" action="/locale/ar">
            @csrf
            <button>العربية</button>
        </form>
    </div>
</div>
```

**Note**: Even when Arabic is selected, the layout remains LTR. Only text content changes.

---

### 8.4 LTR-Only Design

**Rationale**: The user requested LTR layout even for Arabic content.

**Implementation**:
- Set `dir="ltr"` in `<html>` tag (never change to RTL)
- All text changes via `__()` helper
- Layout, navigation, forms remain left-to-right
- Arabic text appears in the same left-aligned structure as English

**Example**:
```html
<html lang="{{ app()->getLocale() }}" dir="ltr">
```

---

## 9. Security & Access Control

### 9.1 Authentication

**Mechanism**: Laravel's built-in authentication (Breeze or custom)

**User Roles** (stored in `users.role`):
- `admin`: Full access
- `teacher`: Limited access (view students, mark attendance, view reports)
- `parent`: View own child's info (optional, future scope)

---

### 9.2 Authorization & Middleware

**Middleware**:
1. **`auth`**: Ensure user is logged in
2. **`RoleMiddleware`**: Check user role
   - `role:admin` - Only admins
   - `role:admin,teacher` - Admins and teachers

**Route Protection**:
```php
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::resource('admin/students', StudentController::class);
    Route::resource('admin/tokens', TokenController::class);
    // ...
});

Route::middleware(['auth', 'role:admin,teacher'])->group(function () {
    Route::get('admin/attendance/mark', [AttendanceController::class, 'mark']);
});
```

---

### 9.3 Token Validation Security

**Public Enrollment Form Security**:
- Token must be valid before showing form
- Re-validate token on form submission (prevent race conditions, replay attacks)
- Use database transactions when marking token as used and creating student
- Rate limiting on enrollment endpoint to prevent abuse

**Middleware**: `CheckTokenValid`
- Validates token in route parameter
- If invalid, redirect to error page
- If valid, proceed to form

---

### 9.4 Input Validation

**Form Requests**:
- `StoreStudentRequest`: Validate student creation (admin)
- `EnrollmentFormRequest`: Validate public enrollment form
- Use Laravel validation rules, custom messages

**Example** (`EnrollmentFormRequest`):
```php
public function rules()
{
    return [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'gender' => 'required|in:male,female',
        'date_of_birth' => 'required|date|before:today',
        'current_address' => 'required|string',
        'class_id' => 'required|exists:classes,id',
        'has_allergy' => 'required|boolean',
        'allergy_type' => 'required_if:has_allergy,true|nullable|string',
        'parent_first_name' => 'required|string',
        // ...
    ];
}
```

---

### 9.5 CSRF Protection

- All forms include `@csrf` token
- Laravel automatically validates CSRF tokens on POST/PUT/DELETE requests

---

### 9.6 SQL Injection Prevention

- Use Eloquent ORM and query builder (parameterized queries)
- Never use raw SQL with user input unless properly escaped

---

### 9.7 XSS Prevention

- Blade `{{ }}` syntax auto-escapes output
- Use `{!! !!}` only for trusted, sanitized HTML

---

## 10. Deployment Guide (cPanel)

### 10.1 Pre-Deployment Checklist

- ✅ All code tested locally
- ✅ `.env.example` file created
- ✅ Database schema and seeders ready
- ✅ No npm dependencies (all assets via CDN)
- ✅ Git repository ready

---

### 10.2 cPanel Deployment Steps

#### Step 1: Upload Files

**Option A: Git (recommended)**
1. SSH into cPanel or use cPanel Git Version Control
2. Clone repository to `public_html/school` or appropriate directory
3. Run `composer install --no-dev --optimize-autoloader`

**Option B: File Upload**
1. Zip entire Laravel project
2. Upload via cPanel File Manager
3. Extract in desired directory

---

#### Step 2: Configure Web Root

**Laravel Structure**:
```
/home/username/school-app/
├── app/
├── public/     <- This should be web root
├── resources/
├── routes/
├── .env
└── ...
```

**cPanel Configuration**:
1. Go to cPanel > Domains > Addon Domains or Subdomains
2. Set Document Root to `/home/username/school-app/public`
3. This ensures `index.php` in `public/` is the entry point

---

#### Step 3: Database Setup

1. cPanel > MySQL Databases
2. Create new database: `schooldb`
3. Create database user: `schooluser` with strong password
4. Assign user to database with all privileges
5. Note: database name, user, password, host (usually `localhost`)

---

#### Step 4: Environment Configuration

1. Copy `.env.example` to `.env`
2. Edit `.env`:
   ```
   APP_NAME="School Management System"
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=https://yourschool.com

   DB_CONNECTION=mysql
   DB_HOST=localhost
   DB_PORT=3306
   DB_DATABASE=schooldb
   DB_USERNAME=schooluser
   DB_PASSWORD=your_secure_password

   SESSION_DRIVER=database
   QUEUE_CONNECTION=database
   ```
3. Generate app key: `php artisan key:generate`

---

#### Step 5: Run Migrations & Seeders

Via SSH or cPanel Terminal:
```bash
cd /home/username/school-app
php artisan migrate --force
php artisan db:seed --force
```

---

#### Step 6: Set Permissions

```bash
chmod -R 755 storage
chmod -R 755 bootstrap/cache
```

---

#### Step 7: Optimize for Production

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

#### Step 8: Create Admin User

**Option A: Seeder**
- Include `UserSeeder` that creates default admin:
  - Email: admin@school.com
  - Password: ChangeMeNow123!

**Option B: Tinker**
```bash
php artisan tinker
User::create([
    'name' => 'Admin',
    'email' => 'admin@school.com',
    'password' => bcrypt('SecurePassword123'),
    'role' => 'admin',
]);
```

---

#### Step 9: Test Application

1. Visit `https://yourschool.com`
2. Test login
3. Test dashboard
4. Generate a token
5. Test public enrollment form with token

---

### 10.3 SSL/HTTPS (cPanel)

1. cPanel > SSL/TLS Status
2. Use AutoSSL or Let's Encrypt to generate free SSL certificate
3. Force HTTPS in `.htaccess` (Laravel public/.htaccess):
   ```apache
   RewriteEngine On
   RewriteCond %{HTTPS} off
   RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
   ```

---

### 10.4 Scheduled Tasks (Cron Jobs)

If using Laravel's task scheduler (e.g., for token expiration checks):

cPanel > Cron Jobs:
```
* * * * * cd /home/username/school-app && php artisan schedule:run >> /dev/null 2>&1
```

---

### 10.5 Backup Strategy

**Database**:
- cPanel > Backup Wizard > Backup database regularly

**Files**:
- cPanel > Backup > Download Full Backup

**Automation** (optional):
- Set up automated backups via cPanel or third-party tools

---

### 10.6 Maintenance Mode

Enable during updates:
```bash
php artisan down --message="Updating system, please check back soon."
```

After updates:
```bash
php artisan up
```

---

## 11. Additional Considerations

### 11.1 Email Notifications (Optional Future Enhancement)

- Send email on successful enrollment
- Password reset emails
- Attendance alerts to parents

**Configuration**: Set up SMTP in `.env` (e.g., Gmail, SendGrid, or cPanel email)

---

### 11.2 File Uploads (Optional)

- Student photos
- Document uploads (birth certificate, previous school records)

**Storage**: Use Laravel's `storage/app/public` with symlink to `public/storage`

---

### 11.3 Reporting & Analytics

- Generate PDF reports (using DomPDF or similar, loaded via CDN or PHP library)
- Student report cards
- Attendance summaries
- Token usage reports

---

### 11.4 Parent Portal (Future Scope)

- Parents can log in (using `parent` role)
- View child's attendance, grades, notices
- Update contact information

---

### 11.5 Performance Optimization

- **Database Indexing**: Index foreign keys, frequently queried columns (admission_number, email)
- **Query Optimization**: Use eager loading (`with()`) to prevent N+1 queries
- **Caching**: Cache dashboard stats, class lists
- **CDN for Assets**: Tailwind, Alpine.js loaded from reliable CDN

---

## 12. Summary

This specification provides a **complete blueprint** for building a modern, Laravel-based School Management System with the following highlights:

✅ **No Build Tools**: Pure Laravel + Blade + CDN assets (Tailwind, Alpine.js)
✅ **cPanel Ready**: Direct deployment without npm install
✅ **Modern UI/UX**: React-like interactivity and design
✅ **Bilingual**: English/Arabic with LTR layout
✅ **Smart Token System**: Controlled enrollment with one-time-use tokens
✅ **Auto-Generated Admission Numbers**: Year-Month-Position logic
✅ **Comprehensive Modules**: Students, Teachers, Classes, Attendance, Tokens
✅ **Secure & Scalable**: Role-based access, input validation, optimized for production

### Next Steps

1. **Database Migrations**: Create migration files based on schema in Section 3
2. **Model Relationships**: Define Eloquent relationships in models
3. **Controllers**: Implement logic for each module as described
4. **Blade Views**: Build layouts and components following UI/UX guidelines
5. **Localization**: Create translation files for EN and AR
6. **Service Classes**: Implement `AdmissionNumberService`, `TokenService`
7. **Testing**: Manual testing of all flows (admin, public enrollment)
8. **Deployment**: Follow cPanel deployment steps
9. **Documentation**: Create user manual for admins and parents

---

**This specification is ready to serve as a complete guide for development. Each section can be expanded into detailed implementation code when ready to build.**

---

*Document Version: 1.0*
*Last Updated: 2025-11-17*
*Author: School Management System Architecture Team*
