<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $sql = "
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
        ";

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS VIDEO");
    }
};
