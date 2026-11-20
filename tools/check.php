<?php
declare(strict_types=1);

$missing = [];
if (version_compare(PHP_VERSION, '8.2', '<')) { $missing[] = 'PHP 8.2 or later'; }
foreach (['intl', 'mbstring', 'fileinfo', 'gd', 'mysqli', 'openssl'] as $extension) {
    if (!extension_loaded($extension)) { $missing[] = $extension; }
}
if ($missing) { fwrite(STDERR, 'Enable/install: ' . implode(', ', $missing) . "\nActive php.ini: " . (php_ini_loaded_file() ?: '(none)') . "\n"); exit(1); }
echo 'PHP ' . PHP_VERSION . " and required extensions are ready.\n";
