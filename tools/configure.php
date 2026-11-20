<?php
declare(strict_types=1);

$root = dirname(__DIR__);
$mode = $argv[1] ?? 'local';
if (!in_array($mode, ['local', 'hosting'], true)) { fwrite(STDERR, "Usage: php tools/configure.php local|hosting\n"); exit(1); }
$file = $mode === 'local' ? $root . '/.env' : $root . '/dist/infinityfree/htdocs/.env';
if (is_file($file)) { echo "An .env file already exists; it was kept.\n"; return; }
$template = $mode === 'local' ? '/.env.example' : '/.env.infinityfree.example';
$content = file_get_contents($root . $template);
$content = str_replace('REPLACE_WITH_A_RANDOM_TOKEN_AT_LEAST_32_CHARACTERS', bin2hex(random_bytes(32)), $content);
if (!is_dir(dirname($file))) { mkdir(dirname($file), 0755, true); }
if (file_put_contents($file, $content) === false) { throw new RuntimeException('Could not write .env.'); }
echo "Created " . ($mode === 'local' ? '.env' : 'dist/infinityfree/htdocs/.env') . ". Edit its connection settings, then use its POS_SETUP_TOKEN at /setup.\n";
