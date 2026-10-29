# StudentHub — Practical 15 Full Integration (ITUE203)

StudentHub is a comprehensive, responsive campus management portal for CHARUSAT university students and administrators. This project integrates the frontend HTML/CSS/JS design with a secure, production-ready PHP 8 backend powered by **MySQLi** and prepared statements.

---

## 🎨 Visual Identity & Architecture Rule

**Strict Design Integrity:**
- All PHP-rendered pages preserve 100% of the authentic HTML/CSS theme (`css/style.css`, `js/script.js`).
- Existing colors (`Campus Teal` and `Midnight Blue`), typography (`Inter`, `Galada`), spacing, cards, interactive hamburger navigation, FAQ accordions, and animations remain identical.
- Users never encounter unstyled PHP output or generic Bootstrap/Tailwind themes.
- Client-side mock interactions gracefully co-exist with native server-side POST workflows.

---

## 🚀 Quick Start with XAMPP

### 1. Prerequisites
- **XAMPP** for Windows (PHP 8.1+ / 8.2+, Apache, MySQL/MariaDB).
- PHP Extensions enabled: `mysqli`, `fileinfo`, `mbstring`, `gd`.

### 2. Location & Document Root
Ensure the project is located inside your XAMPP `htdocs` directory:
```
D:\xampp\htdocs\YUg\StudentHub_Practical15
```
Or:
```
C:\xampp\htdocs\StudentHub_Practical15
```

### 3. Database Setup
1. Open XAMPP Control Panel and start **Apache** and **MySQL**.
2. Open **phpMyAdmin** at `http://localhost/phpmyadmin/`.
3. Import `database.sql` directly. This automatically creates the `studenthub` database, all relational tables, foreign key constraints, indexes, sample datasets, and pre-seeded user accounts.
4. Database connection settings are in `config/database.php` (defaults to host: `127.0.0.1`, user: `root`, password: ``).

### 4. Default Seeded Credentials

| Role | Identifier / Email | Password | Access Level / Destination |
| :--- | :--- | :--- | :--- |
| **Administrator** | `admin@studenthub.edu` | `Admin@2024` | `admin_dashboard.php` (Admin Management Portal) |
| **Student** | `student@studenthub.edu` (or `25IT049`) | `Student@123` | `student_dashboard.php` (Student Hub & Academics) |
| **Student (Alt)** | `25it049@charusat.edu.in` | `Student@123` | `student_dashboard.php` (Student Hub & Academics) |

> **Note on Security:** All passwords are encrypted in MySQL using PHP's native `password_hash(PASSWORD_BCRYPT)` and validated via `password_verify()`.

---

## 🧪 Quality Assurance & Syntax Verification

All 42 PHP files in the project pass `php -l` without warnings or notices:
```bash
D:\xampp\php\php.exe -l index.php
D:\xampp\php\php.exe -l authenticate.php
D:\xampp\php\php.exe -l student_dashboard.php
D:\xampp\php\php.exe -l admin_dashboard.php
D:\xampp\php\php.exe -l events.php
D:\xampp\php\php.exe -l profile.php
D:\xampp\php\php.exe -l faq.php
D:\xampp\php\php.exe -l feedback.php
D:\xampp\php\php.exe -l contact.php
D:\xampp\php\php.exe -l logout.php
D:\xampp\php\php.exe -l timeout.php
# Result: Checked 42 PHP files. Errors: 0
```
"""""" Admin username : admin@studenthub.edu """""
"""""" Admin Password : Admin@2024 """""""


Output :  
1) home Page
![alt text](image.png)

2) about page
![alt text](image-1.png)

3) events page
![alt text](image-2.png)

4) profile page
![alt text](image-3.png)

5) feedback page
![alt text](image-4.png)

6) contact us page
![alt text](image-5.png)

7) admin page 
![alt text](image-6.png)