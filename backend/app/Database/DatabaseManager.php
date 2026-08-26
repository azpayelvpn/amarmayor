<?php

declare(strict_types=1);

namespace AmarMayor\Database;

use AmarMayor\Support\Config;
use AmarMayor\Support\Logger;
use PDO;
use PDOException;
use RuntimeException;
use Throwable;

/**
 * PDO-based MySQL Database Manager with explicit transaction support.
 */
class DatabaseManager
{
    private static ?PDO $pdo = null;

    public static function getConnection(): PDO
    {
        if (self::$pdo === null) {
            self::$pdo = self::createConnection();
        }
        return self::$pdo;
    }

    public static function setConnection(?PDO $pdo): void
    {
        self::$pdo = $pdo;
    }

    public static function beginTransaction(): bool
    {
        return self::getConnection()->beginTransaction();
    }

    public static function commit(): bool
    {
        return self::getConnection()->commit();
    }

    public static function rollBack(): bool
    {
        if (self::getConnection()->inTransaction()) {
            return self::getConnection()->rollBack();
        }
        return false;
    }

    public static function inTransaction(): bool
    {
        return self::getConnection()->inTransaction();
    }

    /**
     * Execute a callback inside an atomic database transaction.
     *
     * @template T
     * @param callable(PDO): T $callback
     * @return T
     * @throws Throwable
     */
    public static function transaction(callable $callback): mixed
    {
        $pdo = self::getConnection();
        $isRootTransaction = !$pdo->inTransaction();

        if ($isRootTransaction) {
            $pdo->beginTransaction();
        }

        try {
            $result = $callback($pdo);
            if ($isRootTransaction && $pdo->inTransaction()) {
                $pdo->commit();
            }
            return $result;
        } catch (Throwable $e) {
            if ($isRootTransaction && $pdo->inTransaction()) {
                $pdo->rollBack();
            }
            Logger::error("Database transaction rolled back due to error: " . $e->getMessage(), ['exception' => $e]);
            throw $e;
        }
    }

    public static function select(string $sql, array $params = []): array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public static function selectOne(string $sql, array $params = []): ?array
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result !== false ? $result : null;
    }

    public static function execute(string $sql, array $params = []): int
    {
        $stmt = self::getConnection()->prepare($sql);
        $stmt->execute($params);
        return $stmt->rowCount();
    }

    public static function insert(string $sql, array $params = []): string|int
    {
        $pdo = self::getConnection();
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $pdo->lastInsertId();
    }

    public static function checkHealth(): array
    {
        try {
            $pdo = self::getConnection();
            $stmt = $pdo->query("SELECT 1 AS ping, VERSION() AS version");
            $row = $stmt ? $stmt->fetch(PDO::FETCH_ASSOC) : null;
            return [
                'status' => 'healthy',
                'connected' => true,
                'version' => $row['version'] ?? 'unknown',
            ];
        } catch (Throwable $e) {
            return [
                'status' => 'unhealthy',
                'connected' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    private static function createConnection(): PDO
    {
        $host = Config::get('database.connections.mysql.host', '127.0.0.1');
        $port = (int)Config::get('database.connections.mysql.port', 3306);
        $database = Config::get('database.connections.mysql.database', 'amarmayor');
        $username = Config::get('database.connections.mysql.username', 'root');
        $password = Config::get('database.connections.mysql.password', '');
        $charset = Config::get('database.connections.mysql.charset', 'utf8mb4');

        $dsn = "mysql:host={$host};port={$port};dbname={$database};charset={$charset}";
        $options = Config::get('database.connections.mysql.options', [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);

        try {
            return new PDO($dsn, $username, $password, $options);
        } catch (PDOException $e) {
            Logger::error("MySQL connection failed: " . $e->getMessage(), ['host' => $host, 'database' => $database]);
            throw new RuntimeException("Database connection failed. Please verify MySQL configuration.", (int)$e->getCode(), $e);
        }
    }
}
