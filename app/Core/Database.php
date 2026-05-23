<?php

declare(strict_types=1);

namespace App\Core;

use PDO;
use PDOStatement;
use RuntimeException;

/**
 * Тонкая обёртка над PDO.
 *
 * Намеренно без репозиториев и QueryBuilder —
 * модели пишут SQL напрямую, это честно и прозрачно.
 */
final class Database
{
    private static ?self $instance = null;
    private PDO $pdo;

    private function __construct(array $config)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'],
            $config['port'],
            $config['database'],
            $config['charset'],
        );

        $this->pdo = new PDO(
            $dsn,
            $config['username'],
            $config['password'],
            $config['options'],
        );
    }

    public static function getInstance(array $config = []): self
    {
        if (self::$instance === null) {
            if (empty($config)) {
                throw new RuntimeException(
                    'Database::getInstance() called without config on first call.'
                );
            }
            self::$instance = new self($config);
        }

        return self::$instance;
    }

    // ── Публичный API ─────────────────────────────────────────────────────────

    /**
     * Выполняет SELECT и возвращает все строки.
     *
     * @param array<string, mixed> $bindings
     * @return list<array<string, mixed>>
     */
    public function fetchAll(string $sql, array $bindings = []): array
    {
        $stmt = $this->run($sql, $bindings);
        return $stmt->fetchAll();
    }

    /**
     * Выполняет SELECT и возвращает одну строку или null.
     *
     * @param array<string, mixed> $bindings
     * @return array<string, mixed>|null
     */
    public function fetchOne(string $sql, array $bindings = []): ?array
    {
        $stmt = $this->run($sql, $bindings);
        $row  = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /**
     * Выполняет INSERT / UPDATE / DELETE.
     * Возвращает количество затронутых строк.
     *
     * @param array<string, mixed> $bindings
     */
    public function execute(string $sql, array $bindings = []): int
    {
        $stmt = $this->run($sql, $bindings);
        return $stmt->rowCount();
    }

    /**
     * Возвращает последний AUTO_INCREMENT id.
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    // ── Транзакции ────────────────────────────────────────────────────────────

    public function beginTransaction(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollback(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Удобная обёртка для транзакций с автоматическим rollback.
     *
     * Пример:
     *   $db->transaction(function() use ($db) {
     *       $db->execute(...);
     *       $db->execute(...);
     *   });
     */
    public function transaction(callable $callback): mixed
    {
        $this->beginTransaction();

        try {
            $result = $callback($this);
            $this->commit();
            return $result;
        } catch (\Throwable $e) {
            $this->rollback();
            throw $e;
        }
    }

    // ── Внутренние методы ─────────────────────────────────────────────────────

    /**
     * @param array<string, mixed> $bindings
     */
    private function run(string $sql, array $bindings): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);

        // Каждый параметр биндим явно с типом —
        // это надёжнее, чем execute(array),
        // и исключает неявное приведение типов PDO.
        foreach ($bindings as $key => $value) {
            $type = match (true) {
                is_int($value)  => PDO::PARAM_INT,
                is_bool($value) => PDO::PARAM_BOOL,
                is_null($value) => PDO::PARAM_NULL,
                default         => PDO::PARAM_STR,
            };

            // Поддерживаем как :name, так и позиционные ?
            $param = is_string($key) ? ":{$key}" : $key + 1;
            $stmt->bindValue($param, $value, $type);
        }

        $stmt->execute();
        return $stmt;
    }
}