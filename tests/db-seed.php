<?php
// DB seeder for tests. Reads common env vars and is tolerant of container hosts.

function env_first(...$keys) {
    foreach ($keys as $k) {
        $v = getenv($k);
        if ($v !== false && $v !== '') return $v;
    }
    return null;
}

$dbHostRaw = env_first('DB_HOST', 'WORDPRESS_DB_HOST', 'MYSQL_HOST') ?: '127.0.0.1:3306';
$dbUser = env_first('DB_USER', 'MYSQL_USER') ?: 'wp';
$dbPass = env_first('DB_PASS', 'MYSQL_PASSWORD') ?: 'wp';
$dbName = env_first('DB_NAME', 'MYSQL_DATABASE') ?: 'wordpress';

// Parse host[:port] if provided
$dbPort = 3306;
$dbHost = $dbHostRaw;
if (strpos($dbHostRaw, ':') !== false) {
    [$h, $p] = explode(':', $dbHostRaw, 2);
    $dbHost = $h;
    if (is_numeric($p)) $dbPort = (int)$p;
}

$mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
if ($mysqli->connect_error) {
    fwrite(STDERR, "DB connection failed: " . $mysqli->connect_error . "\n");
    exit(1);
}

// Ensure the options table exists before attempting to seed
$res = $mysqli->query("SHOW TABLES LIKE 'wp_options'");
if (! $res || $res->num_rows === 0) {
    fwrite(STDERR, "wp_options table not found. Is the WP schema installed?\n");
    $mysqli->close();
    exit(1);
}

// Insert a marker option for tests. Use INSERT IGNORE to avoid duplicates.
$sql = "INSERT IGNORE INTO wp_options (option_name, option_value, autoload) VALUES ('aps_test_seed', '1', 'no')";
if (! $mysqli->query($sql)) {
    fwrite(STDERR, "Seed query failed: " . $mysqli->error . "\n");
    $mysqli->close();
    exit(1);
}

echo "Seed complete\n";

$mysqli->close();
