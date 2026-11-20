<?php
declare(strict_types=1);

// Hydrate official starter configuration after Composer installs the pinned framework.
// Application files supplied by this project are never overwritten.
$root = dirname(__DIR__);
$framework = $root . '/vendor/codeigniter4/framework';
if (!is_file($framework . '/system/Boot.php')) {
    fwrite(STDERR, "Run composer install first.\n");
    exit(1);
}
function copyMissing(string $source, string $target): void
{
    if (!is_dir($target)) { mkdir($target, 0755, true); }
    foreach (new DirectoryIterator($source) as $item) {
        if ($item->isDot()) { continue; }
        $dest = $target . '/' . $item->getFilename();
        if ($item->isDir()) { copyMissing($item->getPathname(), $dest); }
        elseif (!file_exists($dest)) { copy($item->getPathname(), $dest); }
    }
}
copyMissing($framework . '/app/Config', $root . '/app/Config');
copyMissing($framework . '/app/Views/errors', $root . '/app/Views/errors');
if (is_dir($framework . '/app/Language')) {
    copyMissing($framework . '/app/Language', $root . '/app/Language');
}
foreach (['cache', 'logs', 'session', 'uploads/products', 'uploads/avatars'] as $dir) {
    if (!is_dir($root . '/writable/' . $dir)) { mkdir($root . '/writable/' . $dir, 0755, true); }
}
echo "Official CodeIgniter starter configuration and writable folders are ready.\n";
