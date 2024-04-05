<?php

    namespace App\Services;

    use PDO;

class TableManagement extends Database
{
    public function borrarTablas()
    {
        $sql = "DROP TABLE IF EXISTS FECHACONSULTA, VIDEO, JUEGO, TOKEN, USUARIO;";
        $this->pdo->exec($sql);
    }

    public function crearTablas()
    {
        $sql = "CREATE TABLE FECHACONSULTA(
                        idFecha SERIAL PRIMARY KEY,
                        fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                    );
    
                    CREATE TABLE JUEGO(
                        position SERIAL,
                        gameId INT PRIMARY KEY,
                        gameName VARCHAR(255),
                        idFecha INTEGER,
                        CONSTRAINT FK_FECHACONSULTA FOREIGN KEY (idFecha) REFERENCES FECHACONSULTA(idFecha)
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
    }

    public function clearTablas()
    {
        $this->pdo->exec("DELETE FROM VIDEO");
        $this->pdo->exec("DELETE FROM JUEGO");
        $this->pdo->exec("DELETE FROM FECHACONSULTA");
    }
}
