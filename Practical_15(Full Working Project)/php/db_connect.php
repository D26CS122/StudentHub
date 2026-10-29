<?php
/**
 * StudentHub - Database Connection Helper
 * Practical 9: Secure User Registration with MySQLi
 */

// Database credentials (default for XAMPP / WAMP)
$db_host = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "studenthub";

// 1. Establish initial connection to MySQL server
$conn = @new mysqli($db_host, $db_user, $db_pass);

if ($conn->connect_error) {
    // Return connection error helper
    function getDbConnectionError() {
        global $conn;
        return "Database connection failed: " . $conn->connect_error . ". Please make sure MySQL is running in XAMPP/WAMP.";
    }
} else {
    // 2. Ensure database exists
    $createDbSql = "CREATE DATABASE IF NOT EXISTS `$db_name` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci";
    $conn->query($createDbSql);

    // 3. Select database
    $conn->select_db($db_name);

    // 4. Ensure users table exists with required fields
    $createTableSql = "CREATE TABLE IF NOT EXISTS `users` (
        `id` INT AUTO_INCREMENT PRIMARY KEY,
        `name` VARCHAR(100) NOT NULL,
        `student_id` VARCHAR(20) NOT NULL UNIQUE,
        `email` VARCHAR(100) NOT NULL UNIQUE,
        `semester` VARCHAR(10) DEFAULT NULL,
        `department` VARCHAR(50) DEFAULT NULL,
        `password` VARCHAR(255) NOT NULL,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    $conn->query($createTableSql);
}
?>
