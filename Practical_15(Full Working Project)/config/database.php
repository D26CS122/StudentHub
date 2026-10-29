<?php
declare(strict_types=1);
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$config = require __DIR__ . '/database.example.php';
$local = __DIR__ . '/database.local.php';
if (is_file($local)) $config = array_merge($config, require $local);
try {
  $db = new mysqli($config['host'], $config['username'], $config['password'], $config['database']);
  $db->set_charset('utf8mb4');
} catch (mysqli_sql_exception $e) {
  http_response_code(500);
  exit('Database unavailable. Start MySQL and import database.sql; check config/database.example.php.');
}
