<?php
use PHPUnit\Framework\TestCase;

class SeedTest extends TestCase
{
    public function test_seeded_option_exists()
    {
        if (getenv('APS_RUN_DB_SEED') !== '1') {
            $this->markTestSkipped('DB seed tests disabled (set APS_RUN_DB_SEED=1 to enable).');
        }

        if (!extension_loaded('mysqli')) {
            $this->markTestSkipped('mysqli extension not available.');
        }

        // Prefer a set of common environment variable names (DB_*, MYSQL_*, WORDPRESS_*)
        $env_first = function (...$keys) {
            foreach ($keys as $k) {
                $v = getenv($k);
                if ($v !== false && $v !== '') return $v;
            }
            return null;
        };

        $dbHostRaw = $env_first('DB_HOST', 'WORDPRESS_DB_HOST', 'MYSQL_HOST') ?: 'db:3306';
        $dbUser = $env_first('DB_USER', 'MYSQL_USER', 'WORDPRESS_DB_USER') ?: 'wp';
        $dbPass = $env_first('DB_PASS', 'MYSQL_PASSWORD', 'WORDPRESS_DB_PASSWORD') ?: 'wp';
        $dbName = $env_first('DB_NAME', 'MYSQL_DATABASE', 'WORDPRESS_DB_NAME') ?: 'wordpress';
        $dbPort = 3306;
        $dbHost = $dbHostRaw;
        if (strpos($dbHostRaw, ':') !== false) {
            [$h, $p] = explode(':', $dbHostRaw, 2);
            $dbHost = $h;
            if (is_numeric($p)) $dbPort = (int)$p;
        }

        $mysqli = new mysqli($dbHost, $dbUser, $dbPass, $dbName, (int)$dbPort);
        if ($mysqli->connect_error) {
            $this->markTestSkipped('DB connection failed: ' . $mysqli->connect_error);
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
