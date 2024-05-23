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

    public function storeToken(string $twitchToken)
    {
        $insertStatement = $this->pdo->prepare('INSERT INTO TOKEN (token) VALUES (?)');
        $insertStatement->execute([$twitchToken]);
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
}
