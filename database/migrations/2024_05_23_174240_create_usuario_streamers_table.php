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
            CREATE TABLE IF NOT EXISTS USUARIO_STREAMERS (
                username VARCHAR(255),
                streamerId VARCHAR(255),
                PRIMARY KEY (username, streamerId),
                FOREIGN KEY (username) REFERENCES USUARIO (username) ON DELETE CASCADE
            );
        ";

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS USUARIO_STREAMERS");
    }
};
