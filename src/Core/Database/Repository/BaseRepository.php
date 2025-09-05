<?php

namespace MIMS\Core\Database\Repository;

use MIMS\Core\Database\DatabaseConnection;
use PDO;
use PDOStatement;
use Exception;

/**
 * Base Repository Class
 * Implements Repository pattern for data access abstraction
 */
abstract class BaseRepository
{
    protected PDO $connection;
    protected string $table;
    protected string $primaryKey = 'id';

    public function __construct()
    {
        $this->connection = DatabaseConnection::getInstance()->getConnection();
    }

    /**
     * Find record by ID
     */
    public function findById(int $id): ?array
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Find all records
     */
    public function findAll(array $conditions = [], array $orderBy = [], int $limit = null, int $offset = null): array
    {
        $sql = "SELECT * FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClause = $this->buildWhereClause($conditions, $params);
            $sql .= " WHERE {$whereClause}";
        }

        if (!empty($orderBy)) {
            $sql .= " ORDER BY " . implode(', ', $orderBy);
        }

        if ($limit !== null) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = $limit;
            
            if ($offset !== null) {
                $sql .= " OFFSET :offset";
                $params[':offset'] = $offset;
            }
        }

        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Insert new record
     */
    public function insert(array $data): int
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":{$col}", $columns);
        
        $sql = "INSERT INTO {$this->table} (" . implode(', ', $columns) . ") 
                VALUES (" . implode(', ', $placeholders) . ")";
        
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $data);
        $stmt->execute();

        return (int) $this->connection->lastInsertId();
    }

    /**
     * Update record
     */
    public function update(int $id, array $data): bool
    {
        $setClause = array_map(fn($col) => "{$col} = :{$col}", array_keys($data));
        
        $sql = "UPDATE {$this->table} SET " . implode(', ', $setClause) . 
               " WHERE {$this->primaryKey} = :id";
        
        $data['id'] = $id;
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $data);
        
        return $stmt->execute();
    }

    /**
     * Delete record
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        $stmt = $this->connection->prepare($sql);
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        
        return $stmt->execute();
    }

    /**
     * Count records
     */
    public function count(array $conditions = []): int
    {
        $sql = "SELECT COUNT(*) FROM {$this->table}";
        $params = [];

        if (!empty($conditions)) {
            $whereClause = $this->buildWhereClause($conditions, $params);
            $sql .= " WHERE {$whereClause}";
        }

        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();

        return (int) $stmt->fetchColumn();
    }

    /**
     * Execute custom query
     */
    protected function executeQuery(string $sql, array $params = []): array
    {
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();

        return $stmt->fetchAll();
    }

    /**
     * Execute custom query returning single result
     */
    protected function executeQuerySingle(string $sql, array $params = []): ?array
    {
        $stmt = $this->connection->prepare($sql);
        $this->bindParams($stmt, $params);
        $stmt->execute();

        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Build WHERE clause from conditions
     */
    private function buildWhereClause(array $conditions, array &$params): string
    {
        $clauses = [];
        
        foreach ($conditions as $column => $value) {
            if (is_array($value)) {
                // Handle IN clause
                $placeholders = [];
                foreach ($value as $i => $val) {
                    $placeholder = ":{$column}_{$i}";
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $val;
                }
                $clauses[] = "{$column} IN (" . implode(', ', $placeholders) . ")";
            } else {
                $placeholder = ":{$column}";
                $clauses[] = "{$column} = {$placeholder}";
                $params[$placeholder] = $value;
            }
        }

        return implode(' AND ', $clauses);
    }

    /**
     * Bind parameters to statement
     */
    private function bindParams(PDOStatement $stmt, array $params): void
    {
        foreach ($params as $key => $value) {
            $paramType = PDO::PARAM_STR;
            
            if (is_int($value)) {
                $paramType = PDO::PARAM_INT;
            } elseif (is_bool($value)) {
                $paramType = PDO::PARAM_BOOL;
            } elseif (is_null($value)) {
                $paramType = PDO::PARAM_NULL;
            }

            $stmt->bindValue($key, $value, $paramType);
        }
    }

    /**
     * Start transaction
     */
    protected function beginTransaction(): bool
    {
        return DatabaseConnection::getInstance()->beginTransaction();
    }

    /**
     * Commit transaction
     */
    protected function commit(): bool
    {
        return DatabaseConnection::getInstance()->commit();
    }

    /**
     * Rollback transaction
     */
    protected function rollback(): bool
    {
        return DatabaseConnection::getInstance()->rollback();
    }
}
