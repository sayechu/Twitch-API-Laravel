<?php

    namespace App\Services;

    use PDO;

class DeleteTableManagement extends Database
{
    public function borrarVideosJuego($gameId)
    {
        $sql = "DELETE FROM VIDEO WHERE gameId = :gameId";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':gameId', $gameId, PDO::PARAM_INT);
        $stmt->execute();
    }
}
