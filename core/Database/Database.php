<?php
/*
 * Copyright (c) 2025 HivePHP OldVkDev
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

namespace HivePHP\Database;

use PDO;
use PDOException;

final class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->validateConfig($config);

        $dsn = sprintf(
            'mysql:host=%s;dbname=%s;charset=%s',
            $config['host'],
            $config['dbname'],
            $config['charset']
        );

        try {
            $this->pdo = new PDO(
                $dsn,
                $config['user'],
                $config['pass'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            $this->logException($e, 'CONNECT', []);
            throw new DatabaseException('Database connection failed');
        }
    }

    /* =========================
       PUBLIC API
       ========================= */

    public function fetch(string $sql, array $params = []): ?array
    {
        $stmt = $this->executeStatement($sql, $params);
        $result = $stmt->fetch();

        return $result === false ? null : $result;
    }

    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->executeStatement($sql, $params);
        return $stmt->fetchAll();
    }

    public function execute(string $sql, array $params = []): bool
    {
        $this->executeStatement($sql, $params);
        return true;
    }

    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }

    /* =========================
       CORE EXECUTOR
       ========================= */

    private function executeStatement(string $sql, array $params): \PDOStatement
    {
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;

        } catch (PDOException $e) {
            $this->logException($e, $sql, $params);
            throw new DatabaseException('Database query failed');
        }
    }

    /* =========================
       VALIDATION
       ========================= */

    private function validateConfig(array $config): void
    {
        $required = ['host', 'dbname', 'charset', 'user', 'pass'];

        foreach ($required as $key) {
            if (!array_key_exists($key, $config)) {
                throw new DatabaseException(
                    "Database config error: missing '{$key}'"
                );
            }
        }

        foreach (['host', 'dbname', 'charset', 'user'] as $key) {
            if (!is_string($config[$key]) || $config[$key] === '') {
                throw new DatabaseException(
                    "Database config error: '{$key}' must be non-empty string"
                );
            }
        }

        // pass:
        // ✔ может быть пустой строкой
        if (!is_string($config['pass'])) {
            throw new DatabaseException(
                "Database config error: 'pass' must be string"
            );
        }
    }

    /* =========================
       LOGGING
       ========================= */

    private function logException(PDOException $e, string $sql, array $params): void
    {
        $message = sprintf(
            "[%s]\n%s\nSQL: %s\nParams: %s\n\n",
            date('Y-m-d H:i:s'),
            $e->getMessage(),
            $sql,
            json_encode($params, JSON_UNESCAPED_UNICODE)
        );

        file_put_contents(
            BASE_PATH . '/storage/logs/database.log',
            $message,
            FILE_APPEND
        );
    }
}