<?php

declare(strict_types=1);

/**
 * Точка входа для сидинга.
 *
 * Запуск:
 *   php database/seed.php           — сидинг с очисткой таблиц
 *   php database/seed.php --fresh   — то же самое (явный флаг)
 *
 * Важно:
 *   Перед запуском должны быть применены миграции:
 *   php database/migrate.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Env;
use Database\Seeders\CategorySeeder;
use Database\Seeders\PostSeeder;

// ── Автозагрузка сидеров (они вне app/, поэтому регистрируем вручную) ────────
spl_autoload_register(function (string $class): void {
    // Database\Seeders\CategorySeeder → database/seeders/CategorySeeder.php
    if (str_starts_with($class, 'Database\\')) {
        $relative = str_replace('Database\\', '', $class);
        $relative = str_replace('\\', DIRECTORY_SEPARATOR, $relative);
        $path     = __DIR__ . DIRECTORY_SEPARATOR . strtolower($relative) . '.php';

        // Приводим к нижнему регистру только директории, не класс
        $parts    = explode(DIRECTORY_SEPARATOR, $relative);
        $file     = array_pop($parts);
        $dirs     = array_map('strtolower', $parts);
        $path     = __DIR__
            . DIRECTORY_SEPARATOR
            . implode(DIRECTORY_SEPARATOR, $dirs)
            . DIRECTORY_SEPARATOR
            . $file
            . '.php';

        if (file_exists($path)) {
            require_once $path;
        }
    }
});

// ── Окружение и конфиг ────────────────────────────────────────────────────────
Env::load(dirname(__DIR__) . '/.env');
$dbConfig = require dirname(__DIR__) . '/config/database.php';

// ── PDO-соединение ────────────────────────────────────────────────────────────
$dsn = sprintf(
    'mysql:host=%s;port=%d;dbname=%s;charset=%s',
    $dbConfig['host'],
    $dbConfig['port'],
    $dbConfig['database'],
    $dbConfig['charset'],
);

try {
    $pdo = new PDO($dsn, $dbConfig['username'], $dbConfig['password'], [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);
} catch (PDOException $e) {
    echo "Connection failed: {$e->getMessage()}\n";
    exit(1);
}

// ── Очистка таблиц ────────────────────────────────────────────────────────────
// TRUNCATE сбрасывает AUTO_INCREMENT — каждый запуск начинается с id=1.
// Порядок важен: сначала дочерние таблицы (FK), затем родительские.
echo "\nTruncating tables...\n";

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');
$pdo->exec('TRUNCATE TABLE post_category');
$pdo->exec('TRUNCATE TABLE posts');
$pdo->exec('TRUNCATE TABLE categories');
$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

echo "Tables truncated.\n\n";

// ── Запуск сидеров ────────────────────────────────────────────────────────────
$seeders = [
    CategorySeeder::class,
    PostSeeder::class,
];

foreach ($seeders as $seederClass) {
    $name   = basename(str_replace('\\', '/', $seederClass));
    echo "Running {$name}...\n";

    /** @var \Database\Seeders\BaseSeeder $seeder */
    $seeder = new $seederClass($pdo);
    $seeder->run();

    echo "\n";
}

echo "Seeding complete.\n\n";