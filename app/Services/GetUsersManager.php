<?php

namespace App\Services;

class GetUsersManager
{
    private DBClient $databaseClient;

    public function __construct(DBClient $databaseClient)
    {
        $this->databaseClient = $databaseClient;
    }

    public function getUsersAndStreamers(): array
    {
        $users = $this->databaseClient->getUsers();
        $result = [];

        foreach ($users as $user) {
            $username = $user['username'];
            $streamers = $this->databaseClient->getStreamers($username);

            $result[] = [
                'username' => $username,
                'followedStreamers' => $streamers
            ];
        }

        return $result;
    }
}
