#!/bin/sh
# Basic PHP-FPM readiness probe:
# - verifies php-fpm process is running
# - verifies a TCP connection can be made to 127.0.0.1:9000 (common PHP-FPM listener)

set -e

if ps aux | grep php-fpm | grep -v grep >/dev/null 2>&1; then
  php -r ' $s = @fsockopen("127.0.0.1", 9000, $e, $str, 1); if ($s) { fclose($s); exit(0); } exit(1);'
else
  exit 1
fi
