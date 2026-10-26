<?php

// Run from the extracted project: php deployment/setup.php
chdir(dirname(__DIR__));
if (PHP_VERSION_ID < 80401) {
    exit("PHP 8.4.1 or newer is required.\n");
}
foreach (['pdo_mysql', 'mbstring', 'openssl', 'fileinfo', 'dom', 'xml', 'bcmath', 'gd'] as $extension) {
    if (!extension_loaded($extension)) {
        exit("Enable PHP extension: $extension\n");
    }
}
if (!is_file('.env')) {
    copy('.env.example', '.env');
    exit("Created .env. Fill DB, SMTP, Yandex and YooKassa settings, then run this command again.\n");
}
require 'vendor/autoload.php';
Dotenv\Dotenv::createUnsafeMutable(getcwd())->load();
function setting(string $key, string $default = ''): string { return $_ENV[$key] ?? $default; }
$database = setting('DB_DATABASE', 'vegetable_base');
if (!preg_match('/^[a-zA-Z0-9_]+$/D', $database)) {
    exit("DB_DATABASE must contain letters, digits and underscores only.\n");
}
try {
    $pdo = new PDO('mysql:host='.setting('DB_HOST', '127.0.0.1').';port='.setting('DB_PORT', '3306').';charset=utf8mb4', setting('DB_USERNAME'), setting('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$database` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $pdo->exec("USE `$database`");
    $count = (int) $pdo->query('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()')->fetchColumn();
    if ($count === 0) {
        $dump = 'deployment/database.sql';
        if (!is_file($dump)) { throw new RuntimeException('deployment/database.sql is missing.'); }
        $pdo->exec(file_get_contents($dump));
        // Session data and one-time tokens must not be reused on the new machine.
        foreach (['sessions', 'password_reset_tokens', 'verification_codes'] as $table) {
            $exists = $pdo->prepare('SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?');
            $exists->execute([$table]);
            if ($exists->fetchColumn()) { $pdo->exec("DELETE FROM `$table`"); }
        }
        echo "Database imported.\n";
    } else {
        echo "Existing database left unchanged; import skipped.\n";
    }
} catch (Throwable $error) {
    fwrite(STDERR, "Database setup failed. Check MySQL, DB settings and permissions. If import was interrupted, use a new empty database.\n");
    exit(1);
}
foreach (['storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'storage/app/private', 'storage/app/public', 'bootstrap/cache'] as $dir) {
    if (!is_dir($dir)) { mkdir($dir, 0775, true); }
}
function artisan(array $args): void {
    $process = proc_open(array_merge([PHP_BINARY, 'artisan'], $args), [STDIN, STDOUT, STDERR], $pipes);
    if (!is_resource($process) || proc_close($process) !== 0) { exit(1); }
}
artisan(['config:clear']);
if (!setting('APP_KEY')) { artisan(['key:generate', '--force']); }
artisan(['migrate', '--force']);
if (!file_exists('public/storage')) {
    // A Windows junction works without elevated symbolic-link privileges.
    if (PHP_OS_FAMILY === 'Windows') {
        $link = str_replace("'", "''", realpath('public').DIRECTORY_SEPARATOR.'storage');
        $target = str_replace("'", "''", realpath('storage/app/public'));
        $command = "New-Item -ItemType Junction -Path '$link' -Target '$target' -ErrorAction Stop | Out-Null";
        $process = proc_open(['powershell.exe', '-NoProfile', '-NonInteractive', '-Command', $command], [STDIN, STDOUT, STDERR], $pipes);
        if (!is_resource($process) || proc_close($process) !== 0) { fwrite(STDERR, "Create public/storage link manually.\n"); exit(1); }
    } else { artisan(['storage:link']); }
}
artisan(['view:cache']);
echo "Ready. Start: php artisan serve --host=127.0.0.1 --port=8000\n";

