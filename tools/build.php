<?php
declare(strict_types=1);

$root = dirname(__DIR__);
if (!is_file($root . '/vendor/codeigniter4/framework/system/Boot.php') || !is_file($root . '/app/Config/App.php')) {
    fwrite(STDERR, "Run Setup-Windows.ps1 or composer install before building.\n"); exit(1);
}
$target = $root . '/dist/infinityfree/htdocs';
if (!is_dir($target)) { mkdir($target, 0755, true); }
function copyTree(string $source, string $dest): void
{
    if (!is_dir($dest)) { mkdir($dest, 0755, true); }
    foreach (new DirectoryIterator($source) as $entry) {
        if ($entry->isDot()) { continue; }
        $out = $dest . '/' . $entry->getFilename();
        if ($entry->isDir()) { copyTree($entry->getPathname(), $out); }
        else { copy($entry->getPathname(), $out); }
    }
}
// A repeated build updates code but preserves its separate hosting .env and
// existing staged uploads. It never copies the local development .env or sessions.
foreach (['app', 'public', 'vendor'] as $dir) { copyTree($root . '/' . $dir, $target . '/' . $dir); }
copy($root . '/.htaccess', $target . '/.htaccess');
foreach (['cache', 'logs', 'session', 'uploads/products', 'uploads/avatars'] as $dir) {
    if (!is_dir($target . '/writable/' . $dir)) { mkdir($target . '/writable/' . $dir, 0755, true); }
}
copy($root . '/writable/.htaccess', $target . '/writable/.htaccess');
copy($root . '/writable/index.html', $target . '/writable/index.html');
$argv[1] = 'hosting';
require __DIR__ . '/configure.php';
echo "Hosting folder: dist/infinityfree/htdocs\nEdit its .env, import database/infinityfree.sql into a new hosted database, then upload the CONTENTS of htdocs by FTP.\n";
