<?php

declare(strict_types=1);

/**
 * Простой migration runner.
 *
 * Логика:
 *   1. Создаём таблицу `migrations`, если её нет.
 *   2. Читаем все *.sql файлы из database/migrations/ по порядку.
 *   3. Пропускаем уже применённые (есть запись в `migrations`).
 *   4. Применяем новые, записываем в `migrations`.
 *
 * Запуск: php database/migrate.php
 */

require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Core\Env;
use App\Core\Database;

Env::load(dirname(__DIR__) . '/.env');

$dbConfig = require dirname(__DIR__) . '/config/database.php';
$db       = Database::getInstance($dbConfig);
$pdo      = (function () use ($db): PDO {
    // Для DDL-операций нам нужен прямой PDO — обёртка не нужна.
    // Получаем его через рефлексию, чтобы не открывать лишний метод в Database.
    $ref = new ReflectionProperty(Database::class, 'pdo');
    $ref->setAccessible(true);
    return $ref->getValue($db);
})();

// ── 1. Таблица для трекинга миграций ─────────────────────────────────────────
$pdo->exec(
    'CREATE TABLE IF NOT EXISTS migrations (
        id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
        migration  VARCHAR(255) NOT NULL,
        applied_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (id),
        UNIQUE KEY uq_migrations_name (migration)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci'
);

// ── 2. Уже применённые миграции ───────────────────────────────────────────────
$applied = $pdo
    ->query('SELECT migration FROM migrations')
    ->fetchAll(PDO::FETCH_COLUMN);
$applied = array_flip($applied); // для O(1) проверки

// ── 3. Читаем файлы миграций ──────────────────────────────────────────────────
$migrationsPath = __DIR__ . '/migrations';
$files          = glob($migrationsPath . '/*.sql');
sort($files); // гарантируем порядок по имени файла (001_, 002_, ...)

if (empty($files)) {
    echo "No migration files found in {$migrationsPath}\n";
    exit(0);
}

// ── 4. Применяем новые ────────────────────────────────────────────────────────
foreach ($files as $file) {
    $name = basename($file);

    if (isset($applied[$name])) {
        echo "  [skip]    {$name}\n";
        continue;
    }

    $sql = file_get_contents($file);

    if ($sql === false || trim($sql) === '') {
        echo "  [empty]   {$name} — пропущен (пустой файл)\n";
        continue;
    }

    try {
        $pdo->exec($sql);

        $stmt = $pdo->prepare(
            'INSERT INTO migrations (migration) VALUES (?)'
        );
        $stmt->execute([$name]);

        echo "  [applied] {$name}\n";
    } catch (PDOException $e) {
        echo "  [ERROR]   {$name}: {$e->getMessage()}\n";
        exit(1);
    }
}

echo "\nMigrations complete.\n";