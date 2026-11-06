<?php
declare(strict_types=1);

/*
 * Local XAMPP configuration. Change these values for your hosting environment.
 */
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = 'localhost';
$username = 'root';
$password = '';
$database = 'food_ordering_db';

try {
    $conn = new mysqli($host, $username, $password, $database);
    $conn->set_charset('utf8mb4');
} catch (mysqli_sql_exception $exception) {
    error_log($exception->getMessage());
    http_response_code(500);
    exit('Database connection failed. Check config.php and make sure MySQL is running.');
}
