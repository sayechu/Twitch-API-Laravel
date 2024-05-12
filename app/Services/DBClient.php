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

    public function getUserFromDatabase(string $userId): array|null
    {
        $stmt = $this->pdo->prepare("SELECT * FROM USUARIO WHERE ID = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$userData) {
            return null;
        }

        $formattedUserData = [
            'id' => $userData['ID'],
            'login' => $userData['login'],
            'display_name' => $userData['displayName'],
            'type' => $userData['type'],
            'broadcaster_type' => $userData['broadcasterType'],
            'description' => $userData['description'],
            'profile_image_url' => $userData['profileImageUrl'],
            'offline_image_url' => $userData['offlineImageUrl'],
            'view_count' => $userData['viewCount'],
            'created_at' => $userData['createdAt']
        ];

        return $formattedUserData;
    }

    public function addUserToDatabase(array $userData): void
    {
        $stmt = $this->pdo->prepare("
                    INSERT INTO USUARIO (
                        ID,
                        login,
                        displayName,
                        type,
                        broadcasterType,
                        description,
                        profileImageUrl,
                        offlineImageUrl,
                        viewCount,
                        createdAt
                    ) VALUES (
                        :ID,
                        :login,
                        :displayName,
                        :type,
                        :broadcasterType,
                        :description,
                        :profileImageUrl,
                        :offlineImageUrl,
                        :viewCount,
                        :createdAt
                    )
            ");

        $stmt->bindParam(':ID', $userData['id']);
        $stmt->bindParam(':login', $userData['login']);
        $stmt->bindParam(':displayName', $userData['display_name']);
        $stmt->bindParam(':type', $userData['type']);
        $stmt->bindParam(':broadcasterType', $userData['broadcaster_type']);
        $stmt->bindParam(':description', $userData['description']);
        $stmt->bindParam(':profileImageUrl', $userData['profile_image_url']);
        $stmt->bindParam(':offlineImageUrl', $userData['offline_image_url']);
        $stmt->bindParam(':viewCount', $userData['view_count']);
        $stmt->bindParam(':createdAt', $userData['created_at']);

        $stmt->execute();
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
