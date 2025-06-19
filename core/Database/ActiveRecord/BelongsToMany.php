<?php

namespace Core\Database\ActiveRecord;

use Core\Database\Database;
use PDO;

class BelongsToMany
{
    public function __construct(
        private Model  $model,
        private string $related,
        private string $pivot_table,
        private string $from_foreign_key,
        private string $to_foreign_key,
    ) {
    }

    /**
     * @return array<Model>
     */
    public function get()
    {
        $fromTable = $this->model::table();
        $toTable = $this->related::table();

        $attributes = $toTable . '.id, ';
        foreach ($this->related::columns() as $column) {
            $attributes .= $toTable . '.' . $column . ', ';
        }
        $attributes = rtrim($attributes, ', ');

        $sql = <<<SQL
            SELECT 
                {$attributes}
            FROM 
                {$fromTable}, {$toTable}, {$this->pivot_table}
            WHERE 
                {$toTable}.id = {$this->pivot_table}.{$this->to_foreign_key} AND
                {$fromTable}.id = {$this->pivot_table}.{$this->from_foreign_key} AND
                {$fromTable}.id = :id
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);

        $stmt->bindValue(':id', $this->model->id);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $models = [];
        foreach ($rows as $row) {
            $models[] = new $this->related($row);
        }

        return $models;
    }

    public function count(): int
    {
        $fromTable = $this->model::table();
        $toTable = $this->related::table();

        $sql = <<<SQL
        SELECT 
            count({$toTable}.id) as total
        FROM 
            {$fromTable}, {$toTable}, {$this->pivot_table}
        WHERE 
            {$toTable}.id = {$this->pivot_table}.{$this->to_foreign_key} AND
            {$fromTable}.id = {$this->pivot_table}.{$this->from_foreign_key} AND
            {$fromTable}.id = :id
        SQL;

        $pdo = Database::getDatabaseConn();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':id', $this->model->id);
        $stmt->execute();
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return $rows[0]['total'];
    }
    
    /**
     * Attach a related model to the pivot table
     * 
     * @param int $id The ID of the related model to attach
     * @param array<string, mixed> $attributes Additional attributes for the pivot table
     * @return bool Success status
     */
    public function attach(int $id, array $attributes = []): bool
    {
        $pdo = Database::getDatabaseConn();
        
        // Check if the relationship already exists
        $checkSql = "SELECT COUNT(*) as count FROM {$this->pivot_table} WHERE {$this->from_foreign_key} = :from_id AND {$this->to_foreign_key} = :to_id";
        $checkStmt = $pdo->prepare($checkSql);
        $checkStmt->bindValue(':from_id', $this->model->id);
        $checkStmt->bindValue(':to_id', $id);
        $checkStmt->execute();
        $result = $checkStmt->fetch(PDO::FETCH_ASSOC);
        
        if ($result['count'] > 0) {
            return false; // Relationship already exists
        }
        
        // Build column and value lists for the SQL query
        $columns = [$this->from_foreign_key, $this->to_foreign_key];
        $values = [':from_id', ':to_id'];
        $params = [
            ':from_id' => $this->model->id,
            ':to_id' => $id
        ];
        
        // Add any additional attributes
        foreach ($attributes as $key => $value) {
            $columns[] = $key;
            $values[] = ":$key";
            $params[":$key"] = $value;
        }
        
        $columnStr = implode(', ', $columns);
        $valueStr = implode(', ', $values);
        
        $sql = "INSERT INTO {$this->pivot_table} ($columnStr) VALUES ($valueStr)";
        $stmt = $pdo->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }
        
        return $stmt->execute();
    }
    
    /**
     * Detach a related model from the pivot table
     * 
     * @param int $id The ID of the related model to detach
     * @return bool Success status
     */
    public function detach(int $id): bool
    {
        $pdo = Database::getDatabaseConn();
        $sql = "DELETE FROM {$this->pivot_table} WHERE {$this->from_foreign_key} = :from_id AND {$this->to_foreign_key} = :to_id";
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':from_id', $this->model->id);
        $stmt->bindValue(':to_id', $id);
        $stmt->execute();
        
        return ($stmt->rowCount() > 0);
    }
}
