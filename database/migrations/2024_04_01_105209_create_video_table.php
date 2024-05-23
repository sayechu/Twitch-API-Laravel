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
                id BIGINT PRIMARY KEY,
                user_id INT,
                user_name VARCHAR(255),
                view_count VARCHAR(255),
                duration VARCHAR(255),
                created_at VARCHAR(255),
                title VARCHAR(255),
                game_id VARCHAR(255),
                game_name VARCHAR(255),

                CONSTRAINT FK_GAME1 FOREIGN KEY (game_id) REFERENCES JUEGO(gameId)
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
