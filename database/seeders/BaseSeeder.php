<?php

declare(strict_types=1);

namespace Database\Seeders;

use PDO;

/**
 * Базовый класс для сидеров.
 *
 * Намеренно работает с raw PDO, а не с Database-обёрткой —
 * сидер запускается из CLI, ему нужен быстрый bulk-insert
 * без лишних слоёв.
 */
abstract class BaseSeeder
{
    public function __construct(
        protected readonly PDO $pdo
    ) {}

    abstract public function run(): void;

    /**
     * Выводит сообщение в stdout с временной меткой.
     */
    protected function log(string $message): void
    {
        $time = date('H:i:s');
        echo "[{$time}] {$message}\n";
    }
}