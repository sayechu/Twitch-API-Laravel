<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS TIMELINE_STREAMS (
                streamerId VARCHAR(255),
                streamerName VARCHAR(255),
                title VARCHAR(255),
                game VARCHAR(255),
                viewerCount INT,
                startedAt VARCHAR(255)
            );
        ";

        DB::statement($sql);
    }

    public function down(): void
    {
        DB::statement("DROP TABLE IF EXISTS TIMELINE_STREAMS");
    }
};
