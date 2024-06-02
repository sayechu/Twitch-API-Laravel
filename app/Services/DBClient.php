
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
    private const INTERNAL_SERVER_ERROR_MESSAGE = "Error del servidor al seguir al streamer";

    public function __construct()
    {
        $this->dataSourceName = "mysql:host=$this->host;port=$this->port;dbname=$this->dbName";
        try {
            $this->pdo = new PDO($this->dataSourceName, $this->username, $this->password);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException) {
            throw new InternalServerErrorException();
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
        $selectStatement = $this->pdo->prepare('SELECT token FROM TOKEN');
        $selectStatement->execute();
        return $selectStatement->fetch(PDO::FETCH_ASSOC)['token'];
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
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE);
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
            throw new InternalServerErrorException();
        }
    }

    /**
     * @throws InternalServerErrorException
     */
    public function userFollowsStreamer(string $username, string $streamerId): bool
    {
        try {
            $selectStatement = $this->pdo->prepare('SELECT COUNT(*) FROM USUARIO_STREAMERS
                                                          WHERE username = ? AND streamerId = ?');
            $selectStatement->execute([$username, $streamerId]);
            $count = $selectStatement->fetchColumn();

            return $count > 0;
        } catch (PDOException) {
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE);
        }
    }

    /**
     * @throws InternalServerErrorException
     */
    public function addUserFollowsStreamer(string $username, string $streamerId): void
    {
        try {
            $insertStatement = $this->pdo->prepare('INSERT INTO USUARIO_STREAMERS
                                                          (username, streamerId) VALUES (?, ?)');
            $insertStatement->execute([$username, $streamerId]);
        } catch (PDOException) {
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_MESSAGE);
        }
    }

    public function isUserFollowingStreamer(string $username, string $streamerId): bool
    {
        try {
            $selectStatement = $this->pdo->prepare('SELECT COUNT(*)
                                                    FROM USUARIO_STREAMERS
                                                    WHERE username = ? AND streamerId = ?');
            $selectStatement->execute([$username, $streamerId]);
            return $selectStatement->fetchColumn() > 0;
        } catch (PDOException) {
            throw new InternalServerErrorException('Error del servidor al dejar de seguir al streamer.');
        }
    }

    public function unfollowStreamer(string $username, string $streamerId): void
    {
        try {
            $deleteStatement = $this->pdo->prepare('DELETE FROM USUARIO_STREAMERS
                                                    WHERE username = ? AND streamerId = ?');
            $deleteStatement->execute([$username, $streamerId]);
        } catch (PDOException) {
            throw new InternalServerErrorException('Error del servidor al dejar de seguir al streamer.');
        }
    }

    public function isGameStored(mixed $gameId): bool
    {
        $selectStatement = $this->pdo->prepare('SELECT COUNT(*) FROM JUEGO WHERE gameId = ?');
        $selectStatement->execute([$gameId]);
        return $selectStatement->fetchColumn() > 0;
    }

    public function storeTopGame(array $topGame): void
    {
        $insertJuegoStatement = $this->pdo->prepare(
            'INSERT INTO JUEGO (gameId, gameName, idFecha) VALUES (?, ?, ?)'
        );
        $insertFechaStatement = $this->pdo->prepare(
            'INSERT INTO FECHACONSULTA (fecha) VALUES (NULL)'
        );
        $insertFechaStatement->execute();
        $insertJuegoStatement->execute([$topGame['id'], $topGame['name'], $this->pdo->lastInsertId()]);
    }

    public function updateTopGameLastUpdateTime(string $gameId): void
    {
        $updateStatement = $this->pdo->prepare('UPDATE FECHACONSULTA
            SET fecha = CURRENT_TIMESTAMP
            WHERE idFecha IN
            (SELECT idFecha
            FROM JUEGO
            WHERE gameId = ?)');
        $updateStatement->execute([$gameId]);
    }

    public function isDataStoredRecentlyFromGame(string $gameId, int $since): bool
    {
        $selectStatement = $this->pdo->prepare('SELECT 1
            FROM JUEGO j
            JOIN FECHACONSULTA fc ON j.idFecha = fc.idFecha
            WHERE j.gameId = ? AND fc.fecha >= NOW() - INTERVAL ? SECOND
            LIMIT 1');
        $selectStatement->execute([$gameId, $since]);
        return $selectStatement->fetch() !== false;
    }

    public function getVideosOfAGivenGame(string $gameId): array
    {
        $selectStatement = $this->pdo->prepare('SELECT * FROM VIDEO WHERE game_id = ?');
        $selectStatement->execute([$gameId]);
        return $selectStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateTopGameVideos(array $topFourtyVideos, string $topGameId, string $gameName): void
    {
        $deleteStatement = $this->pdo->prepare('DELETE FROM VIDEO WHERE game_id = ?');
        $deleteStatement->execute([$topGameId]);

        $insertStatement = $this->pdo->prepare(
            'INSERT INTO VIDEO (
                        id,
                        user_id,
                        user_name,
                        view_count,
                        duration,
                        created_at,
                        title,
                        game_id,
                        game_name
                    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)'
        );

        foreach ($topFourtyVideos as $video) {
            $insertStatement->execute([
                $video['id'],
                $video['user_id'],
                $video['user_name'],
                $video['view_count'],
                $video['duration'],
                $video['created_at'],
                $video['title'],
                $topGameId,
                $gameName
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

    public function isLoadedDB(): bool
    {
        $selectStatement = $this->pdo->prepare("SELECT COUNT(*) FROM JUEGO");
        $selectStatement->execute();
        return $selectStatement->fetchColumn() > 0;
    }
}
