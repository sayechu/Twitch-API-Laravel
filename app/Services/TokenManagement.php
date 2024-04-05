<?php

    namespace App\Services;

    use PDO;

class TokenManagement extends Database
{
    public function existeTokenDB()
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM TOKEN");
        $stmt->execute();
        return ($stmt->fetchColumn() > 0);
    }

    public function getTokenDB()
    {
        $stmt = $this->pdo->query("SELECT token FROM TOKEN ORDER BY tokenId DESC LIMIT 1");
        $tokenData = $stmt->fetch(PDO::FETCH_ASSOC);
        return ($tokenData !== false) ? $tokenData['token'] : null;
    }

    public function insertarToken($newToken)
    {
        $stmt = $this->pdo->prepare("INSERT INTO TOKEN (token) VALUES (?)");
        $stmt->execute([$newToken]);
    }
}
