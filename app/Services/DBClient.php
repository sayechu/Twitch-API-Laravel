<?php

namespace App\Services;

use App\Exceptions\InternalServerErrorException;
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

    public function storeToken(string $twitchToken): void
    {
        $insertStatement = $this->pdo->prepare('INSERT INTO TOKEN (token) VALUES (?)');
        $insertStatement->execute([$twitchToken]);
    }

    /**
     * @throws InternalServerErrorException
     */
    public function checkIfUsernameExists(string $username): bool
    {
        try {
            $selectStatement = $this->pdo->prepare('SELECT COUNT(*) FROM USUARIO WHERE username = ?');
            $selectStatement->execute([$username]);
            return $selectStatement->fetchColumn() > 0;
        } catch (PDOException) {
            throw new InternalServerErrorException("Error del servidor al crear el usuario");
        }
    }

    /**
     * @throws InternalServerErrorException
     */
    public function createUser(string $username, string $password): void
    {
        try {
            $insertStatement = $this->pdo->prepare('INSERT INTO USUARIO (username, password) VALUES (?, ?)');
            $insertStatement->execute([$username, $password]);
        } catch (PDOException) {
            throw new InternalServerErrorException("Error del servidor al crear el usuario");
        }
    }
    public function isLoadedDB(): bool
    {
        $selectStatement = $this->pdo->prepare("SELECT COUNT(*) FROM JUEGO");
        $selectStatement->execute();
        return $selectStatement->fetchColumn() > 0;
    }

    public function addTopThreeGamesToDB(array $topThreeGames): void
    {
        $insertJuegoStatement = $this->pdo->prepare(
            'INSERT INTO JUEGO (gameId, gameName, idFecha) VALUES (?, ?, ?)'
        );
        $insertFechaStatement = $this->pdo->prepare(
            'INSERT INTO FECHACONSULTA (fecha) VALUES (DEFAULT)'
        );

        foreach ($topThreeGames as $topGame) {
            $insertFechaStatement->execute();
            $idFecha = $this->pdo->lastInsertId();

            $gameId = $topGame['id'];
            $gameName = $topGame['name'];
            $insertJuegoStatement->execute([$gameId, $gameName, $idFecha]);
        }
    }

    public function addVideosToDB(array $topFourtyVideos, string $gameId): void
    {
        $insertStatement = $this->pdo->prepare(
            'INSERT INTO VIDEO (
                        videoId,
                        userId,
                        userName,
                        visitas,
                        duracion,
                        fecha,
                        titulo,
                        gameId
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
        );

        foreach ($topFourtyVideos as $video) {
            $videoId = $video['id'];
            $userId = $video['user_id'];
            $userName = $video['user_name'];
            $viewCount = $video['view_count'];
            $duration = $video['duration'];
            $createdAt = $video['created_at'];
            $title = $video['title'];

            $insertStatement->execute([
                $videoId,
                $userId,
                $userName,
                $viewCount,
                $duration,
                $createdAt,
                $title,
                $gameId
            ]);
        }
    }

    public function getOldestUpdateDatetime(): mixed
    {
        $stmt = $this->pdo->query("SELECT MIN(FC.fecha) AS fecha
                                FROM JUEGO J
                                INNER JOIN FECHACONSULTA FC ON J.idFecha = FC.idFecha");
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function getTopThreeGames(): array
    {
        $selectStatement = 'SELECT J.gameId, J.gameName, FC.fecha
                            FROM JUEGO J
                            INNER JOIN FECHACONSULTA FC ON J.idFecha = FC.idFecha';
        $stmt = $this->pdo->prepare($selectStatement);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function deleteVideosOfAGivenGame(string $gameId): void
    {
        $deleteStatement = "DELETE FROM VIDEO WHERE gameId = :gameId";
        $stmt = $this->pdo->prepare($deleteStatement);
        $stmt->bindParam(':gameId', $gameId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function updateDatetime(string $gameId): void
    {
        $updateStatement = "UPDATE FECHACONSULTA
            SET fecha = CURRENT_TIMESTAMP
            WHERE idFecha IN (SELECT idFecha FROM JUEGO WHERE gameId = :idGame)";
        $stmt = $this->pdo->prepare($updateStatement);
        $stmt->bindParam(':idGame', $gameId, PDO::PARAM_INT);
        $stmt->execute();
    }

    public function getGameIdAtPosition(int $position): string
    {
        $selectStatement = "SELECT J.gameId
                            FROM JUEGO J
                            WHERE J.position = :pos";
        $stmt = $this->pdo->prepare($selectStatement);
        $stmt->bindParam(':pos', $position, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC)[0]['gameId'];
    }

    public function updateTopGame(int $position, string $gameId, string $gameName): void
    {
        $updateStatement = "UPDATE JUEGO SET gameId = ?, gameName = ? WHERE position = ?";

        $stmt = $this->pdo->prepare($updateStatement);

        $stmt->bindParam(1, $gameId, PDO::PARAM_INT);
        $stmt->bindParam(2, $gameName, PDO::PARAM_STR);
        $stmt->bindParam(3, $position, PDO::PARAM_INT);

        $stmt->execute();
        $this->updateDatetime($gameId);
    }

    public function getTopsOfTheTopsAttributes($gameId): array
    {
        $queryStatement = "WITH UserVideos AS (
                    SELECT
                        V.userId,
                        V.userName AS user_name,
                        COUNT(*) AS total_videos,
                        SUM(V.visitas) AS total_views,
                        MAX(V.visitas) AS MaxVisitas
                    FROM VIDEO V
                    WHERE V.gameId = $gameId
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
                            AND V.gameId = $gameId
                            AND V.visitas = UV.MaxVisitas
                        LIMIT 1
                    ) AS most_viewed_title,
                    UV.MaxVisitas AS most_viewed_views,
                    (
                        SELECT V.duracion
                        FROM VIDEO V
                        WHERE V.userId = UV.userId
                            AND V.gameId = $gameId
                            AND V.visitas = UV.MaxVisitas
                        LIMIT 1
                    ) AS most_viewed_duration,
                    (
                        SELECT V.fecha
                        FROM VIDEO V
                        WHERE V.userId = UV.userId
                            AND V.gameId = $gameId
                            AND V.visitas = UV.MaxVisitas
                        LIMIT 1
                    ) AS most_viewed_created_at
                FROM UserVideos UV
                ORDER BY UV.MaxVisitas DESC
                LIMIT 1;
            ";

        $queryAttributes = $this->pdo->query($queryStatement);
        return $queryAttributes->fetch(PDO::FETCH_ASSOC);
    }
}
