<?php

namespace App\Core;

/**
 * Base Model
 * Active Record pattern simple
 */
abstract class Model
{
    protected Database $db;
    protected string $table;
    protected string $primaryKey = 'id';
    
    public function __construct()
    {
        $this->db = Database::getInstance();
    }
    
    /**
     * Trouve tous les enregistrements
     */
    public function all(): array
    {
        $sql = "SELECT * FROM {$this->table}";
        return $this->db->query($sql);
    }
    
    /**
     * Trouve un enregistrement par ID
     */
    public function find(int $id)
    {
        $sql = "SELECT * FROM {$this->table} WHERE {$this->primaryKey} = :id LIMIT 1";
        return $this->db->queryOne($sql, ['id' => $id]);
    }
    
    /**
     * Trouve des enregistrements selon des critères
     */
    public function where(string $column, $value): array
    {
        // Sanitize column name to prevent SQL injection
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $sql = "SELECT * FROM {$this->table} WHERE `{$column}` = :value";
        return $this->db->query($sql, ['value' => $value]);
    }
    
    /**
     * Trouve un seul enregistrement selon des critères
     */
    public function findWhere(string $column, $value)
    {
        $column = preg_replace('/[^a-zA-Z0-9_]/', '', $column);
        $sql = "SELECT * FROM {$this->table} WHERE `{$column}` = :value LIMIT 1";
        return $this->db->queryOne($sql, ['value' => $value]);
    }
    
    /**
     * Insère un nouvel enregistrement
     */
    public function create(array $data): int
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$this->table} ({$columns}) VALUES ({$placeholders})";
        $this->db->execute($sql, $data);
        
        return (int) $this->db->lastInsertId();
    }
    
    /**
     * Met à jour un enregistrement
     */
    public function update(int $id, array $data): bool
    {
        $setParts = [];
        foreach (array_keys($data) as $column) {
            $setParts[] = "{$column} = :{$column}";
        }
        $setClause = implode(', ', $setParts);
        
        $sql = "UPDATE {$this->table} SET {$setClause} WHERE {$this->primaryKey} = :id";
        $data['id'] = $id;
        
        return $this->db->execute($sql, $data);
    }
    
    /**
     * Supprime un enregistrement
     */
    public function delete(int $id): bool
    {
        $sql = "DELETE FROM {$this->table} WHERE {$this->primaryKey} = :id";
        return $this->db->execute($sql, ['id' => $id]);
    }
    
    /**
     * Compte le nombre d'enregistrements
     */
    public function count(string $column = '*'): int
    {
        $sql = "SELECT COUNT({$column}) as total FROM {$this->table}";
        $result = $this->db->queryOne($sql);
        return (int) $result['total'];
    }
    
    /**
     * Retourne l'instance de la base de données
     */
    public function getDb(): Database
    {
        return $this->db;
    }
    
    /**
     * Exécute une requête SQL personnalisée (méthode publique)
     */
    public function rawQuery(string $sql, array $params = []): array
    {
        return $this->db->query($sql, $params);
    }
    
    /**
     * Exécute une requête SQL personnalisée
     */
    public  function query(string $sql, array $params = []): array
    {
        return $this->db->query($sql, $params);
    }
    
    /**
     * Exécute une requête SQL personnalisée (une seule ligne)
     */
    protected function queryOne(string $sql, array $params = [])
    {
        return $this->db->queryOne($sql, $params);
    }
}
