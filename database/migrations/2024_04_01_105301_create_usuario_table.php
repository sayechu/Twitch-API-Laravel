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
            );
        ";

        DB::statement($sql);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS USUARIO");
    }
};
