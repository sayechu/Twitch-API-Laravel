<?php

namespace App\Services;

class Database
{
    private $host = 'mysql';
    private $port = '3306';
    private $dbname = 'laravel';
    private $username = 'sail';
    private $password = 'password';
    private $dsn;
    private $pdo;

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
        } catch (\PDOException $e) {
            echo "Error de conexión: " . $e->getMessage();
        }
    }

    public function borrarTablas()
    {
        // Sentencia para borrar -> EJECUTAR
        $sql = "DROP TABLE IF EXISTS FECHACONSULTA, VIDEO, JUEGO, TOKEN, USUARIO;";
        $this->pdo->exec($sql);
        echo "Tablas eliminadas\n";
    }

    public function crearTablas()
    {
        // Sentencia creacion tablas -> EJECUTAR
        $sql = "CREATE TABLE JUEGO(
                    position SERIAL,
                    gameId INT PRIMARY KEY,
                    gameName VARCHAR(255)
                );
    
                CREATE TABLE VIDEO(
                    videoId INT PRIMARY KEY,
                    userId INT,
                    userName VARCHAR(255),
                    visitas INT,
                    duracion VARCHAR(255),
                    fecha VARCHAR(255),
                    titulo VARCHAR(255),
                    gameId INT,
            
                    CONSTRAINT FK_GAME1 FOREIGN KEY (gameId) REFERENCES JUEGO(gameId)
                );
    
                CREATE TABLE FECHACONSULTA(
                    fecha TIMESTAMP
                );
                
                CREATE TABLE TOKEN(
                    tokenId SERIAL,
                    token VARCHAR(255) PRIMARY KEY
                );
                
                CREATE TABLE USUARIO(
                    ID VARCHAR(255),
                    login VARCHAR(255),
                    displayName VARCHAR(255),
                    type VARCHAR(255),
                    broadcasterType VARCHAR(255),
                    description VARCHAR(255),
                    profileImageUrl VARCHAR(255),
                    offlineImageUrl VARCHAR(255),
                    viewCount INT,
                    createdAt VARCHAR(255)
                );";

        $this->pdo->exec($sql);
        echo "Tablas creadas\n";
    }

    public function obtenerFechaUltimaInsercion()
    {
        $stmt = $this->pdo->query("SELECT fecha FROM FECHACONSULTA ORDER BY fecha DESC LIMIT 1");
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public function existeTokenDB()
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM TOKEN");
        $stmt->execute();
        return ($stmt->fetchColumn() > 0);
    }

    public function getTokenDB()
    {
        $stmt = $this->pdo->query("SELECT token FROM TOKEN ORDER BY tokenId DESC LIMIT 1");
        $tokenData = $stmt->fetch(\PDO::FETCH_ASSOC);
        return ($tokenData !== false) ? $tokenData['token'] : null;
    }

    public function insertarToken($newToken)
    {
        $stmt = $this->pdo->prepare("INSERT INTO TOKEN (token) VALUES (?)");
        $stmt->execute([$newToken]);
    }

    public function clearTablas()
    {
        $this->pdo->exec("TRUNCATE TABLE VIDEO CASCADE; TRUNCATE TABLE JUEGO CASCADE; TRUNCATE TABLE FECHACONSULTA;");
        //$this->pdo->exec("INSERT INTO FECHACONSULTA (fecha) VALUES (NOW())");
        $fechaActual = date('Y-m-d H:i:s');
        $stmt = $this->pdo->prepare("INSERT INTO FECHACONSULTA (fecha) VALUES (:fecha)");
        $stmt->bindParam(':fecha', $fechaActual, \PDO::PARAM_STR);
        $stmt->execute();
    }

    public function insertarTopGames($topGamesData)
    {
        $stmt = $this->pdo->prepare("INSERT INTO JUEGO (gameId, gameName) VALUES (?, ?)");

        // Iterar sobre los 3 juegos más populares
        foreach ($topGamesData['data'] as $game) {
            // Obtener el ID y el nombre del juego
            $gameId = $game['id'];
            $gameName = $game['name'];

            $stmt->execute([$gameId, $gameName]);
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

    public function obtenerAtributos($gameId)
    {
        // Preparar sentencia -> EJECUTAR
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

    public function obtenerNombreJuegoEId()
    {
        $sql = "SELECT gameId, gameName FROM JUEGO";
        $stmt = $this->pdo->prepare($sql);
        $success = $stmt->execute();
        return $stmt;
    }

    public function comprobarIdUsuarioEnDB($userId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM USUARIO WHERE ID = (?)");
        $stmt->execute([$userId]);
        return ($stmt->fetchColumn() > 0);
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

    public function devolverUsuarioDeBD($userId)
    {
        $stmt = $this->pdo->prepare("SELECT * FROM USUARIO WHERE ID = ?");
        $stmt->execute([$userId]);
        $userData = $stmt->fetch(\PDO::FETCH_ASSOC);

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
}
