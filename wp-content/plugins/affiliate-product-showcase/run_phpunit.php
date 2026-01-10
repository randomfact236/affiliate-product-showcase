<?php
// Wrapper to ensure project root autoload is used when running PHPUnit from the plugin directory
// Load root-level autoload (some packages are installed at project root)
$rootAutoload = __DIR__ . '/../../..' . '/vendor/autoload.php';
if (file_exists($rootAutoload)) {
	require $rootAutoload;
}

// Load plugin-local autoload to include dev tools placed in this directory
$localAutoload = __DIR__ . '/vendor/autoload.php';
if (file_exists($localAutoload)) {
	require $localAutoload;
}

require __DIR__ . '/vendor/phpunit/phpunit/phpunit';
