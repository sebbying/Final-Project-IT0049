<?php
// For the local automated HTTP tests only. Production uses Apache .htaccess.
$public = realpath(__DIR__ . '/../public');
$urlPath = rawurldecode(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH) ?: '/');
$file = realpath($public . $urlPath);
if ($file && str_starts_with($file, $public . DIRECTORY_SEPARATOR) && is_file($file) && !preg_match('~(^|/)\.~', $urlPath)) { return false; }
require $public . '/index.php';
