<?php

namespace App\Services;

use App\Exceptions\NotFoundException;

class UnfollowManager
{
    private DBClient $databaseClient;

    public function __construct(DBClient $databaseClient)
    {
        $this->databaseClient = $databaseClient;
    }

    public function unfollowStreamer(string $username, string $streamerId): array
    {
        if ($this->databaseClient->isUserFollowingStreamer($username, $streamerId)) {
            $this->databaseClient->unfollowStreamer($username, $streamerId);
            return ["message" => "Dejaste de seguir a {$streamerId}"];
        }

        throw new NotFoundException(
            "El usuario ({$username}) o el streamer ({$streamerId}) especificado no existe en la API."
        );
    }
}
