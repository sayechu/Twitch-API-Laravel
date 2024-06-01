<?php

namespace App\Services;

use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;

class UnfollowManager
{
    private DBClient $databaseClient;

    public function __construct(DBClient $databaseClient)
    {
        $this->databaseClient = $databaseClient;
    }

    public function unfollowStreamer(string $username, string $streamerId): void
    {
        if ($this->databaseClient->isUserFollowingStreamer($username, $streamerId)) {
            $this->databaseClient->unfollowStreamer($username, $streamerId);
            return;
        }
        throw new NotFoundException("El usuario ( userId ) o el streamer ( streamerId )
                                            especificado no existe en la API.");
    }
}
