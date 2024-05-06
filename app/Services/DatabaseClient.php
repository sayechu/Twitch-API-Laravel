<?php

namespace App\Services;

use PDO;
use PDOException;

class DatabaseClient
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

    public function getUserFromDatabase($user)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM USUARIO WHERE ID = ?");
        $stmt->execute([$user]);
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

    public function addUserToDatabase($api_reponse_array)
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

        $stmt->bindParam(':ID', $api_reponse_array['id']);
        $stmt->bindParam(':login', $api_reponse_array['login']);
        $stmt->bindParam(':displayName', $api_reponse_array['display_name']);
        $stmt->bindParam(':type', $api_reponse_array['type']);
        $stmt->bindParam(':broadcasterType', $api_reponse_array['broadcaster_type']);
        $stmt->bindParam(':description', $api_reponse_array['description']);
        $stmt->bindParam(':profileImageUrl', $api_reponse_array['profile_image_url']);
        $stmt->bindParam(':offlineImageUrl', $api_reponse_array['offline_image_url']);
        $stmt->bindParam(':viewCount', $api_reponse_array['view_count']);
        $stmt->bindParam(':createdAt', $api_reponse_array['created_at']);

        $stmt->execute();
    }
}
