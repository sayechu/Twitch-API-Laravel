<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = "
            CREATE TABLE JUEGO (
                position SERIAL,
                gameId VARCHAR(255) PRIMARY KEY,
                gameName VARCHAR(255),
                idFecha BIGINT UNSIGNED,
                CONSTRAINT FK_FECHACONSULTA FOREIGN KEY (idFecha) REFERENCES FECHACONSULTA(idFecha)
            )
        ";

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS JUEGO");
    }
};
