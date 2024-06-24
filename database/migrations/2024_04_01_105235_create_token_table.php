<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sql = "
            CREATE TABLE TOKEN(
                tokenId SERIAL,
                token VARCHAR(255) PRIMARY KEY
            );
        ";

        DB::statement($sql);
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS TOKEN");
    }
};
