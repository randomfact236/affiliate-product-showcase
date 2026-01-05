<?php
use PHPUnit\Framework\TestCase;

class SeedTest extends TestCase
{
    public function test_seeded_option_exists()
    {
        $dbHost = getenv('DB_HOST') ?: 'db';
        $dbPort = getenv('DB_PORT') ?: 3306;
        $dbUser = getenv('DB_USER') ?: 'wp';
        $dbPass = getenv('DB_PASS') ?: 'wp';
        $dbName = getenv('DB_NAME') ?: 'wordpress';

        $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
        if ($mysqli->connect_error) {
            $this->fail('DB connection failed: ' . $mysqli->connect_error);
        }

        $stmt = $mysqli->prepare("SELECT option_value FROM wp_options WHERE option_name = ? LIMIT 1");
        $this->assertNotFalse($stmt, 'Prepare statement failed: ' . $mysqli->error);
        $name = 'aps_test_seed';
        $stmt->bind_param('s', $name);
        $stmt->execute();
        $stmt->bind_result($value);
        $found = $stmt->fetch();
        $stmt->close();
        $mysqli->close();

        $this->assertNotFalse($found, 'Seeded option not found in wp_options');
        $this->assertNotNull($value, 'Seeded option value is null');
    }
}
