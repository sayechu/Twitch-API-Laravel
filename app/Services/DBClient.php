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
    private const INTERNAL_SERVER_ERROR_UNFOLLOW_MESSAGE = 'Error del servidor al dejar de seguir al streamer.';

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
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_FOLLOW_MESSAGE);
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
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_FOLLOW_MESSAGE);
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
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_FOLLOW_MESSAGE);
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

    public function getStreamers(string $username): array
    {
        try {
            $selectStatement = $this->pdo->prepare('SELECT streamerId FROM USUARIO_STREAMERS WHERE username = ?');
            $selectStatement->execute([$username]);
            return $selectStatement->fetchAll(PDO::FETCH_COLUMN);
        } catch (PDOException) {
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_FOLLOW_MESSAGE);
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
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_FOLLOW_MESSAGE);
        }
    }

    public function unfollowStreamer(string $username, string $streamerId): void
    {
        try {
            $deleteStatement = $this->pdo->prepare('DELETE FROM USUARIO_STREAMERS
                                                    WHERE username = ? AND streamerId = ?');
            $deleteStatement->execute([$username, $streamerId]);
        } catch (PDOException) {
            throw new InternalServerErrorException(self::INTERNAL_SERVER_ERROR_UNFOLLOW_MESSAGE);
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
                $stream['viewer_count'],
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
}
