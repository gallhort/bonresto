<?php

class DatabasePDO {
    /** @var PDO */
    private $pdo;

    /**
     * Construct: tries to reuse global $dbh if available, otherwise builds a PDO from env or given DSN
     * @param string|null $dsn
     * @param string|null $user
     * @param string|null $pass
     * @param array $options
     */
    public function __construct($dsn = null, $user = null, $pass = null, $options = []) {
        if (isset($GLOBALS['dbh']) && $GLOBALS['dbh'] instanceof PDO) {
            $this->pdo = $GLOBALS['dbh'];
            return;
        }

        $host = getenv('DB_HOST') ?: 'localhost';
        $db = getenv('DB_NAME') ?: 'lebonresto';
        $charset = getenv('DB_CHARSET') ?: 'utf8mb4';

        if (!$dsn) {
            $dsn = "mysql:host={$host};dbname={$db};charset={$charset}";
        }

        $user = $user ?? getenv('DB_USER');
        $pass = $pass ?? getenv('DB_PASS');

        $defaultOpts = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ];

        $options = $options + $defaultOpts;

        $this->pdo = new PDO($dsn, $user, $pass, $options);
    }

    public function getPdo() {
        return $this->pdo;
    }

    public function prepare($sql) {
        return $this->pdo->prepare($sql);
    }

    public function fetchAll($sql, array $params = []) {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    }

    public function fetch($sql, array $params = []) {
        $stmt = $this->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetch();
    }

    public function execute($sql, array $params = []) {
        $stmt = $this->prepare($sql);
        return $stmt->execute($params);
    }

    public function lastInsertId() {
        return $this->pdo->lastInsertId();
    }
}
