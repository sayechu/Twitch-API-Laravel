<?php

namespace App\Services;

use App\Exceptions\ConflictException;
use App\Exceptions\InternalServerErrorException;

class GetUsersManager
{
    private DBClient $dbClient;

    public function __construct(DBClient $dbClient)
    {
        $this->dbClient = $dbClient;
    }
    public function getUsersAndStreamers(): array
    {
        $users = $this->dbClient->getUsers();
        $result = [];

        foreach ($users as $user) {
            $username = $user['username'];
            $streamers = $this->dbClient->getStreamers($username);

            $result[] = [
                "username" => $username,
                "followedStreamers" => $streamers
            ];
        }

        return $result;
    }
}
