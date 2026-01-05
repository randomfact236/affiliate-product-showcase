<?php
// Simple DB readiness probe for WordPress healthcheck.
// Tries to connect using mysqli with environment variables.

$host = getenv('WORDPRESS_DB_HOST') ?: getenv('DB_HOST') ?: 'db:3306';
$parts = explode(':', $host);
$hostname = $parts[0];
$port = isset($parts[1]) && is_numeric($parts[1]) ? (int)$parts[1] : 3306;
$user = getenv('WORDPRESS_DB_USER') ?: getenv('MYSQL_USER') ?: 'root';
$pass = getenv('WORDPRESS_DB_PASSWORD') ?: getenv('MYSQL_PASSWORD') ?: '';
$db   = getenv('WORDPRESS_DB_NAME') ?: getenv('MYSQL_DATABASE') ?: 'wordpress';

$conn = @mysqli_connect($hostname, $user, $pass, $db, $port);
if ($conn) {
    mysqli_close($conn);
    exit(0);
}
exit(1);
