CREATE DATABASE IF NOT EXISTS studenthub CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE studenthub;
CREATE TABLE IF NOT EXISTS users (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE,
 password VARCHAR(255) NOT NULL, role ENUM('student','admin') NOT NULL DEFAULT 'student',
 student_id VARCHAR(30) NULL UNIQUE, course VARCHAR(100) NULL, department VARCHAR(100) NULL, semester VARCHAR(20) NULL, year_level TINYINT UNSIGNED NULL,
 gender VARCHAR(30) NULL, mobile VARCHAR(20) NULL, phone VARCHAR(20) NULL, address VARCHAR(255) NULL, bio TEXT NULL, dob VARCHAR(50) NULL, status ENUM('active','inactive') NOT NULL DEFAULT 'active',
 created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP, last_login DATETIME NULL,
 INDEX idx_users_role_status(role,status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS students (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL UNIQUE, student_id VARCHAR(30) NOT NULL UNIQUE,
 name VARCHAR(100) NOT NULL, email VARCHAR(190) NOT NULL UNIQUE, mobile VARCHAR(20) NULL,
 course VARCHAR(100) NOT NULL, year_level TINYINT UNSIGNED NOT NULL DEFAULT 1,
 status ENUM('active','inactive') NOT NULL DEFAULT 'active', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_students_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_students_course(course), INDEX idx_students_status(status), INDEX idx_students_name(name)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS events (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, title VARCHAR(180) NOT NULL, description TEXT NOT NULL,
 category VARCHAR(80) NOT NULL DEFAULT 'General', event_date DATETIME NOT NULL, venue VARCHAR(180) NOT NULL,
 capacity INT UNSIGNED NOT NULL DEFAULT 100, poster VARCHAR(255) NULL, status ENUM('published','draft','closed') NOT NULL DEFAULT 'published',
 created_by INT UNSIGNED NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
 CONSTRAINT fk_events_creator FOREIGN KEY(created_by) REFERENCES users(id) ON DELETE SET NULL,
 UNIQUE KEY uq_events_title_date(title,event_date),
 INDEX idx_events_date_status(event_date,status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS registrations (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NOT NULL, event_id INT UNSIGNED NOT NULL,
 status ENUM('registered','cancelled','attended') NOT NULL DEFAULT 'registered', registered_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 UNIQUE KEY uq_registration_user_event(user_id,event_id),
 CONSTRAINT fk_reg_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE CASCADE,
 CONSTRAINT fk_reg_event FOREIGN KEY(event_id) REFERENCES events(id) ON DELETE CASCADE,
 INDEX idx_reg_status(status)
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS feedback (
 id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, name VARCHAR(100) NOT NULL,
 email VARCHAR(190) NOT NULL, subject VARCHAR(180) NOT NULL, message TEXT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_feedback_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB;
CREATE TABLE IF NOT EXISTS audit_logs (
 id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY, user_id INT UNSIGNED NULL, action VARCHAR(80) NOT NULL,
 entity VARCHAR(80) NOT NULL, entity_id INT NULL, ip_address VARCHAR(45) NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 CONSTRAINT fk_audit_user FOREIGN KEY(user_id) REFERENCES users(id) ON DELETE SET NULL,
 INDEX idx_audit_created(created_at), INDEX idx_audit_action(action)
) ENGINE=InnoDB;
-- Seed demonstration users (Admin and Student accounts with password_hash)
INSERT IGNORE INTO users (id, name, email, password, role, student_id, course, year_level, gender, mobile, status) VALUES
(1, 'System Administrator', 'admin@studenthub.edu', '$2y$10$MloNGbhMHBilcLe5bXchbulvRSamvzZm4tCXPlDJMUS0w79.66ScC', 'admin', 'ADM001', 'Administration', 4, 'Other', '9898000000', 'active'),
(2, 'Tirth Ladani', 'student@studenthub.edu', '$2y$10$hm7ksKp5mzOMfkdW6s.f5uyfVrn2i0zeli2zwuJlHHTDadlqGhLJW', 'student', '25IT049', 'Information Technology', 2, 'Male', '9898000001', 'active'),
(3, 'Tirth Ladani', '25it049@charusat.edu.in', '$2y$10$hm7ksKp5mzOMfkdW6s.f5uyfVrn2i0zeli2zwuJlHHTDadlqGhLJW', 'student', '25IT049-ALT', 'Information Technology', 2, 'Male', '9898000002', 'active');

-- 20 safe demonstration students (no account passwords are seeded).
INSERT IGNORE INTO students(student_id,name,email,mobile,course,year_level,status) VALUES
('SH26001','Aarav Patel','aarav.patel@example.test','9000000001','Computer Science',2,'active'),
('SH26002','Diya Shah','diya.shah@example.test','9000000002','Information Technology',2,'active'),
('SH26003','Vivaan Mehta','vivaan.mehta@example.test','9000000003','Computer Science',3,'active'),
('SH26004','Anaya Desai','anaya.desai@example.test','9000000004','Electronics',1,'active'),
('SH26005','Reyansh Joshi','reyansh.joshi@example.test','9000000005','Information Technology',2,'active'),
('SH26006','Ira Trivedi','ira.trivedi@example.test','9000000006','Computer Science',1,'active'),
('SH26007','Advik Parmar','advik.parmar@example.test','9000000007','Mechanical',3,'active'),
('SH26008','Saanvi Modi','saanvi.modi@example.test','9000000008','Civil',2,'active'),
('SH26009','Krishna Rao','krishna.rao@example.test','9000000009','Information Technology',1,'active'),
('SH26010','Myra Nair','myra.nair@example.test','9000000010','Computer Science',3,'active'),
('SH26011','Arjun Shah','arjun.shah@example.test','9000000011','Electronics',2,'active'),
('SH26012','Aadhya Patel','aadhya.patel@example.test','9000000012','Information Technology',3,'active'),
('SH26013','Dhruv Mehta','dhruv.mehta@example.test','9000000013','Mechanical',1,'active'),
('SH26014','Kiara Desai','kiara.desai@example.test','9000000014','Civil',2,'active'),
('SH26015','Atharv Joshi','atharv.joshi@example.test','9000000015','Computer Science',1,'active'),
('SH26016','Pari Trivedi','pari.trivedi@example.test','9000000016','Information Technology',2,'inactive'),
('SH26017','Kabir Modi','kabir.modi@example.test','9000000017','Electronics',3,'active'),
('SH26018','Navya Rao','navya.rao@example.test','9000000018','Civil',1,'active'),
('SH26019','Rudra Nair','rudra.nair@example.test','9000000019','Mechanical',2,'active'),
('SH26020','Sara Parmar','sara.parmar@example.test','9000000020','Computer Science',3,'active');
INSERT INTO events(title,description,category,event_date,venue,capacity,status)
SELECT * FROM (
SELECT 'Web Development Workshop' AS title, 'Hands-on HTML, CSS and JavaScript lab.' AS description, 'Workshop' AS category, '2027-01-15 10:00:00' AS event_date, 'CS Lab 1' AS venue, 80 AS capacity, 'published' AS status
UNION ALL SELECT 'TechFest Opening','Opening ceremony and keynote.','Festival','2027-01-20 09:30:00','Main Auditorium',400,'published'
UNION ALL SELECT 'Hackathon 2027','Build useful prototypes with a team.','Competition','2027-02-05 08:00:00','Innovation Centre',120,'published'
UNION ALL SELECT 'Career Guidance','Career paths and interview preparation.','Seminar','2027-02-12 14:00:00','Seminar Hall',100,'published'
UNION ALL SELECT 'Open Source Day','Explore collaborative software projects.','Workshop','2027-02-20 11:00:00','CS Lab 2',70,'published'
UNION ALL SELECT 'AI Essentials','Introduction to practical AI concepts.','Seminar','2027-03-01 10:00:00','Room A-201',90,'published'
UNION ALL SELECT 'Cyber Safety Clinic','Security awareness and safe computing.','Workshop','2027-03-10 13:00:00','CS Lab 1',60,'published'
UNION ALL SELECT 'Design Sprint','Rapid UI prototyping challenge.','Competition','2027-03-18 09:00:00','Design Studio',50,'published'
UNION ALL SELECT 'Alumni Connect','Meet graduates and ask career questions.','Networking','2027-04-02 15:00:00','Main Auditorium',180,'published'
UNION ALL SELECT 'Cloud Computing 101','Cloud concepts and deployment basics.','Seminar','2027-04-10 10:00:00','Room B-105',90,'published'
UNION ALL SELECT 'Robotics Showcase','Student robotics demos.','Exhibition','2027-04-20 11:00:00','Innovation Centre',150,'published'
UNION ALL SELECT 'Data Day','Data visualization and analytics activities.','Workshop','2027-05-05 10:00:00','CS Lab 3',70,'published'
UNION ALL SELECT 'Green Campus Drive','Student-led campus sustainability event.','Community','2027-05-12 08:00:00','Central Lawn',100,'published'
UNION ALL SELECT 'Project Expo','Showcase semester projects.','Exhibition','2027-05-25 09:00:00','Main Auditorium',300,'published'
UNION ALL SELECT 'Student Wellness Hour','Wellness and study-life balance session.','Community','2027-06-02 12:00:00','Student Centre',80,'published'
) AS seed
WHERE NOT EXISTS (SELECT 1 FROM events e WHERE e.title=seed.title AND e.event_date=seed.event_date);

-- Demo student account (user #2) also appears in the students list used by the admin dashboard.
INSERT IGNORE INTO students(user_id,student_id,name,email,mobile,course,year_level,status) VALUES
(2,'25IT049','Tirth Ladani','student@studenthub.edu','9898000001','Information Technology',2,'active');

-- Sample event registrations for the demo student so the student dashboard has content.
INSERT IGNORE INTO registrations(user_id,event_id,status)
SELECT 2,id,'registered' FROM events WHERE title IN ('Web Development Workshop','Hackathon 2027');

-- Login accounts for the sample students above (every student in the database can sign in
-- with Student ID or email + password; sample accounts share the default password in README.md).
INSERT IGNORE INTO users(name,student_id,email,password,role,course,department,year_level,status)
SELECT s.name,s.student_id,s.email,'$2y$10$hm7ksKp5mzOMfkdW6s.f5uyfVrn2i0zeli2zwuJlHHTDadlqGhLJW','student',s.course,s.course,s.year_level,'active'
FROM students s WHERE s.user_id IS NULL;
UPDATE IGNORE students s JOIN users u ON u.role='student' AND (u.student_id=s.student_id OR u.email=s.email)
SET s.user_id=u.id WHERE s.user_id IS NULL;
