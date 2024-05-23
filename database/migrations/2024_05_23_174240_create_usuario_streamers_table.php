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
            CREATE TABLE USUARIO_STREAMERS (
                username VARCHAR(255),
                password VARCHAR(255) NOT NULL,
                streamerId INT,
                PRIMARY KEY (username, streamerId)
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
