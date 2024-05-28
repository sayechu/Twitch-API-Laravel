<?php

namespace App\Services;

class UnfollowManager
{
    private DBClient $databaseClient;

    public function __construct(DBClient $databaseClient)
    {
        $this->databaseClient = $databaseClient;
    }

    public function unfollowStreamer(string $userId, string $streamerId): void
    {
        // Comprobar si Usuario en DB
        // Comprobar si usuario sigue a Streamer
        // Dejar de seguir
    }
}
