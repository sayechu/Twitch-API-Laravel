<?php

    namespace App\Services;

    use PDO;

class VerificateManagement extends Database
{
    public function comprobarIdUsuarioEnDB($userId)
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM USUARIO WHERE ID = (?)");
        $stmt->execute([$userId]);
        return ($stmt->fetchColumn() > 0);
    }

    public function isLoadedDatabase()
    {
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM JUEGO");
        $stmt->execute();
        return ($stmt->fetchColumn() > 0);
    }
}
