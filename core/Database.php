<?php
declare(strict_types=1);

namespace HivePHP;

use PDO;
use PDOException;
use PDOStatement;

class Database
{
    private PDO $pdo;

    public function __construct(array $config)
    {
        $this->pdo = $this->connect($config);
    }

    /**
     * Создаём подключение
     */
    private function connect(array $config): PDO
    {
        $dsn = sprintf(
            "%s:host=%s;dbname=%s;charset=%s",
            $config['driver'] ?? 'mysql',
            $config['host'],
            $config['database'],
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            return new PDO(
                $dsn,
                $config['username'],
                $config['password'],
                [
                    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ]
            );
        } catch (PDOException $e) {
            throw new \RuntimeException("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Универсальный запрос
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Получить одну строку
     */
    public function fetch(string $sql, array $params = []): ?array
    {
        $row = $this->query($sql, $params)->fetch();
        return $row ?: null;
    }

    /**
     * Получить несколько строк
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        return $this->query($sql, $params)->fetchAll();
    }

    /**
     * Вставка с возвратом ID
     */
    public function insert(string $sql, array $params = []): int
    {
        $this->query($sql, $params);
        return $this->lastInsertId();
    }

    /**
     * Update / Delete
     */
    public function execute(string $sql, array $params = []): int
    {
        return $this->query($sql, $params)->rowCount();
    }

    /**
     * Вернуть ID последней вставленной записи
     */
    public function lastInsertId(): int
    {
        return (int) $this->pdo->lastInsertId();
    }

    /**
     * Транзакции
     */
    public function begin(): void
    {
        $this->pdo->beginTransaction();
    }

    public function commit(): void
    {
        $this->pdo->commit();
    }

    public function rollBack(): void
    {
        $this->pdo->rollBack();
    }

    /**
     * Получить PDO, если нужно что-то продвинутое
     */
    public function pdo(): PDO
    {
        return $this->pdo;
    }
}
