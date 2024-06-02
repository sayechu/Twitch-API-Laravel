<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sql = "
            CREATE TABLE VIDEO(
                id VARCHAR(255) PRIMARY KEY,
                user_id VARCHAR(255),
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

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS VIDEO");
    }
};
