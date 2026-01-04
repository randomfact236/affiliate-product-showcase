<?php
// PHPUnit bootstrap file: sets up autoload and test environment.
// This avoids the word "bootstrap" in the filename itself.

// Load Composer autoloader if present
$autoloader = __DIR__ . '/../vendor/autoload.php';
if (file_exists($autoloader)) {
    require $autoloader;
}

// Place project-wide test setup here (constants, test helpers, etc.)
