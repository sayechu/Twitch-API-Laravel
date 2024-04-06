<?php

    namespace App\Services;

    use PDO;

class SelectTableManagement extends Database
{
    public function devolverUsuarioDeBD($userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM USUARIO WHERE ID = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        $userData = array(
            'id' => $userData['id'],
            'login' => $userData['login'],
            'display_name' => $userData['displayname'],
            'type' => $userData['type'],
            'broadcaster_type' => $userData['broadcastertype'],
            'description' => $userData['description'],
            'profile_image_url' => $userData['profileimageurl'],
            'offline_image_url' => $userData['offlineimageurl'],
            'view_count' => $userData['viewcount'],
            'created_at' => $userData['createdat']
        );

        return $userData;
    }

    public function obtenerIdNombreFechadeJuegos()
    {
        $sql = "SELECT J.gameId, J.gameName, FC.fecha
                    FROM JUEGO J
                    INNER JOIN FECHACONSULTA FC ON J.idFecha = FC.idFecha";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    public function getOldestUpdateDatetime()
    {
        $stmt = $this->pdo->query("SELECT MIN(FC.fecha) AS fecha 
                                FROM JUEGO J
                                INNER JOIN FECHACONSULTA FC ON J.idFecha = FC.idFecha");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }


    public function obtenerAtributos($gameId)
    {
        $sql = "WITH UserVideos AS (
                    SELECT
                        V.userId,
                        V.userName AS user_name,
                        COUNT(*) AS total_videos,
                        SUM(V.visitas) AS total_views,
                        MAX(V.visitas) AS MaxVisitas
                    FROM VIDEO V
                    WHERE V.gameId = {$gameId}
                    GROUP BY V.userId, V.userName
                )
                SELECT
                    UV.userId,
                    UV.user_name,
                    UV.total_videos,
                    UV.total_views,
                    (
                        SELECT V.titulo 
                        FROM VIDEO V 
                        WHERE V.userId = UV.userId 
                            AND V.gameId = {$gameId} 
                            AND V.visitas = UV.MaxVisitas 
                        LIMIT 1
                    ) AS most_viewed_title,
                    UV.MaxVisitas AS most_viewed_views,
                    (
                        SELECT V.duracion 
                        FROM VIDEO V 
                        WHERE V.userId = UV.userId 
                            AND V.gameId = {$gameId} 
                            AND V.visitas = UV.MaxVisitas 
                        LIMIT 1
                    ) AS most_viewed_duration,
                    (
                        SELECT V.fecha 
                        FROM VIDEO V 
                        WHERE V.userId = UV.userId 
                            AND V.gameId = {$gameId} 
                            AND V.visitas = UV.MaxVisitas 
                        LIMIT 1
                    ) AS most_viewed_created_at
                FROM UserVideos UV
                ORDER BY UV.MaxVisitas DESC
                LIMIT 1;
            ";

        return $this->pdo->query($sql);
    }

    public function obtenerGameIdporPosicion($pos) {
        $sql = "SELECT J.gameId
                FROM JUEGO J
                WHERE J.position = :pos";
        try {
            $stmt = $this->pdo->prepare($sql);
            $stmt->bindParam(':pos', $pos, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
    
}
