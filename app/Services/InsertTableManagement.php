<?php

    namespace App\Services;

    use PDO;

class InsertTableManagement extends Database
{
    public function insertarTopGames($topGamesData)
    {
        $stmtJuego = $this->pdo->prepare("INSERT INTO JUEGO (gameId, gameName, idFecha) VALUES (?, ?, ?)");

        foreach ($topGamesData['data'] as $game) {
            $sql = "INSERT INTO FECHACONSULTA (fecha) VALUES (DEFAULT)";
            $this->pdo->exec($sql);

            $idFechaStmt = $this->pdo->query("SELECT MAX(idFecha) FROM FECHACONSULTA");
            $idFecha = $idFechaStmt->fetchColumn();

            $gameId = $game['id'];
            $gameName = $game['name'];
            $stmtJuego->execute([$gameId, $gameName, $idFecha]);
        }
    }

    public function insertarVideos($topVideosData, $gameId)
    {
        $stmt = $this->pdo->prepare(
            "INSERT INTO VIDEO (videoId, userId, userName, visitas, duracion, fecha, titulo, gameId) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
        );
        foreach ($topVideosData['data'] as $video) {
            $videoId = $video['id'];
            $userId = $video['user_id'];
            $username = $video['user_name'];
            $visitas = $video['view_count'];
            $duracion = $video['duration'];
            $fecha = $video['created_at'];
            $titulo = $video['title'];

            $stmt->execute([$videoId, $userId, $username, $visitas, $duracion, $fecha, $titulo, $gameId]);
        }
    }

    public function anadirUsuarioADb($api_reponse_array)
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
