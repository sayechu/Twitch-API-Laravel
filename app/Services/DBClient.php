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
    private const INTERNAL_SERVER_ERROR_FOLLOW_MESSAGE = "Error del servidor al seguir al streamer";
    private const INTERNAL_SERVER_ERROR_MESSAGE = "Error del servidor al seguir al streamer";

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

    public function createUser(string $username, string $password): void
    {
        try {
            $insertStatement = $this->pdo->prepare('INSERT INTO USUARIO (username, password) VALUES (?, ?)');
            $insertStatement->execute([$username, $password]);
        } catch (PDOException) {
            throw new InternalServerErrorException();
        }
    }

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

    public function getUsers(): array
    {
        try {
            $selectStatement = $this->pdo->prepare('SELECT username FROM USUARIO');
            $selectStatement->execute();
            return $selectStatement->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException) {
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_FOLLOW_MESSAGE);
        }
    }

    public function isUserStored(string $username): bool
    {
        $selectStatement = $this->pdo->prepare("SELECT COUNT(*) FROM USUARIO WHERE username = ?");
        $selectStatement->execute([$username]);
        return $selectStatement->fetchColumn() > 0;
    }

    public function getStreamers(string $username): array
    {
        $selectStatement = $this->pdo->prepare('SELECT streamerId FROM USUARIO_STREAMERS WHERE username = ?');
        $selectStatement->execute([$username]);
        return $selectStatement->fetchAll(PDO::FETCH_COLUMN);
    }

    public function storeStreams(mixed $streamerStreams): void
    {
        $insertStatement = $this->pdo->prepare('INSERT INTO TIMELINE_STREAMS
                                                    (streamerId,
                                                     streamerName,
                                                     title,
                                                     game,
                                                     viewerCount,
                                                     startedAt)
                                                    VALUES (?, ?, ?, ?, ?, ?)
                                                    ');
        foreach ($streamerStreams as $stream) {
            $insertStatement->execute([$stream['user_id'],
                $stream['user_name'],
                $stream['title'],
                null,
                $stream['view_count'],
                $stream['created_at']
            ]);
        }
    }

    public function getTimelineStreams(): array
    {
        $selectStatement = $this->pdo->prepare('SELECT * FROM TIMELINE_STREAMS ORDER BY startedAt DESC LIMIT 5;');
        $selectStatement->execute();
        $this->clearTable();
        return $selectStatement->fetchAll(PDO::FETCH_ASSOC);
    }

    private function clearTable(): void
    {
        $deleteStatement = $this->pdo->prepare('DELETE FROM TIMELINE_STREAMS');
        $deleteStatement->execute();
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
}
