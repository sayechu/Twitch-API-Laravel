<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS USUARIO (
                username VARCHAR(255),
                password VARCHAR(255) NOT NULL,
                PRIMARY KEY (username)
            );
        ";

        DB::statement($sql);
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS USUARIO");
    }
};
