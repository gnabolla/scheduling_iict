<?php

class Database
{
    private $connection;

    public function __construct($config, $username = 'root', $password = '')
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%d;dbname=%s;charset=%s',
            $config['host'] ?? 'localhost',
            $config['port'] ?? 3306,
            $config['dbname'] ?? '',
            $config['charset'] ?? 'utf8mb4'
        );

        try {
            $this->connection = new PDO($dsn, $username, $password, [
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions
            ]);
        } catch (PDOException $e) {
            // Handle connection errors gracefully
            throw new Exception('Database connection error: ' . $e->getMessage());
        }
    }

    public function query($query, $params = [])
    {
        $statement = $this->connection->prepare($query);
        $statement->execute($params);
        return $statement;
    }

    // Getter for the PDO connection if needed elsewhere
    public function getConnection()
    {
        return $this->connection;
    }

}
