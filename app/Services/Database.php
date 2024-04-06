<?php

namespace App\Services;

use PDO;
use PDOException;

class Database
{
    private $host = 'mysql';
    private $port = '3306';
    private $dbname = 'laravel';
    private $username = 'sail';
    private $password = 'password';
    private $dsn;
    protected $pdo;

    public function __construct()
    {
        $this->dsn = "mysql:host=$this->host;"
            . "port=$this->port;"
            . "dbname=$this->dbname";
        try {
            $this->pdo = new PDO($this->dsn, $this->username, $this->password);
            if (!$this->pdo) {
                echo "Error de conexión: No se pudo conectar a la DB: $this->dbname";
            }
        } catch (PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
    }

}
