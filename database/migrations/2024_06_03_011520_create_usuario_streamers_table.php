<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS USUARIO_STREAMERS (
                username VARCHAR(255),
                streamerId VARCHAR(255),
                PRIMARY KEY (username, streamerId),
                FOREIGN KEY (username) REFERENCES USUARIO (username) ON DELETE CASCADE
            );
        ";

        DB::statement($sql);
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS USUARIO_STREAMERS");
    }
};
