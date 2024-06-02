<?php

namespace App\Services;

use App\Exceptions\InternalServerErrorException;
use App\Exceptions\NotFoundException;

class TimelineStreamersProvider
{
    private DBClient $databaseClient;

    public function __construct(DBClient $databaseClient)
    {
        $this->databaseClient = $databaseClient;
    }

    /**
     * @throws NotFoundException
     * @throws InternalServerErrorException
     */
    public function getTimelineStreamers(string $username): array
    {
        if (!$this->databaseClient->isUserStored($username)) {
            throw new NotFoundException("El usuario especificado ( {$username} ) no existe.");
        }
        return $this->databaseClient->getStreamers($username);
    }
}
