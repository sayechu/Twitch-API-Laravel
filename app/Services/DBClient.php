<?php

namespace App\Services;

use PDO;
use PDOException;

class DBClient
{
    private string $host = 'mysql';
    private string $port = '3306';
    private string $dbName = 'laravel';
    private string $username = 'sail';
    private string $password = 'password';
    private string $dataSourceName;
    protected PDO $pdo;

    public function __construct()
    {
        $this->dataSourceName = "mysql:host=$this->host;port=$this->port;dbname=$this->dbName";
        try {
            $this->pdo = new PDO($this->dataSourceName, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
    }

    public function isTokenStoredInDatabase(): bool
    {
        $selectStatement = $this->pdo->prepare('SELECT COUNT(*) FROM TOKEN');
        $selectStatement->execute();
        return $selectStatement->fetchColumn() > 0;
    }

    public function getToken(): string
    {
        $stmt = $this->pdo->prepare('SELECT token FROM TOKEN');
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC)['token'];
    }

    public function storeToken(string $twitchToken)
    {
        $insertStatement = $this->pdo->prepare('INSERT INTO TOKEN (token) VALUES (?)');
        $insertStatement->execute([$twitchToken]);
    }
}
