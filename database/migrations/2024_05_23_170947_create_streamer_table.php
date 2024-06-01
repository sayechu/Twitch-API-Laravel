<?php

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        $sql = "
            CREATE TABLE STREAMER(
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

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS STREAMER");
    }
};
