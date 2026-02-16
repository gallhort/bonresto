<?php

namespace App\Core;

use PDO;
use PDOException;

/**
 * Database Connection Handler
 * Singleton PDO wrapper
 */
class Database
{
    private static ?Database $instance = null;
    private PDO $pdo;
    
    /**
     * Constructor privé pour Singleton
     */
    private function __construct()
    {
        $host = getenv('DB_HOST') ?: 'localhost';
        $name = getenv('DB_NAME') ?: 'lebonresto';
        $user = getenv('DB_USER') ?: 'root';
        $pass = getenv('DB_PASS') ?: '';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';
        
        $dsn = "mysql:host={$host};dbname={$name};charset={$charset}";
        
        try {
            $this->pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES => false,
                PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES {$charset}"
            ]);
        } catch (PDOException $e) {
            error_log('Database connection failed: ' . $e->getMessage());
            throw new \Exception('Erreur de connexion à la base de données');
        }
    }
    
    /**
     * Retourne l'instance unique (Singleton)
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new Database();
        }
        
        return self::$instance;
    }
    
    /**
     * Retourne l'objet PDO
     */
    public function getPdo(): PDO
    {
        return $this->pdo;
    }
    
    /**
     * Prépare une requête
     */
    public function prepare(string $sql)
    {
        return $this->pdo->prepare($sql);
    }
    
    /**
     * Exécute une requête et retourne toutes les lignes
     */
    public function query(string $sql, array $params = []): array
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }
    
    /**
     * Exécute une requête et retourne une seule ligne
     */
    public function queryOne(string $sql, array $params = [])
    {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }
    
    /**
     * Exécute une requête INSERT/UPDATE/DELETE
     */
    public function execute(string $sql, array $params = []): bool
    {
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }
    
    /**
     * Retourne le dernier ID inséré
     */
    public function lastInsertId(): string
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Démarre une transaction
     */
    public function beginTransaction(): bool
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit une transaction
     */
    public function commit(): bool
    {
        return $this->pdo->commit();
    }
    
    /**
     * Rollback une transaction
     */
    public function rollback(): bool
    {
        return $this->pdo->rollBack();
    }
}
