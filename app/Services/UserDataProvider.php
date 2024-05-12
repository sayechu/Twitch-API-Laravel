<?php

namespace App\Services;

class UserDataProvider
{
    private ApiClient $apiClient;
    private DBClient $databaseClient;

    public function __construct(ApiClient $apiClient, DBClient $databaseClient)
    {
        $this->databaseClient = $databaseClient;
        $this->apiClient = $apiClient;
    }

    public function getUserResponse(string $userId, string $twitchToken) : array
    {
        $apiUrl = "https://api.twitch.tv/helix/users?id=" . urlencode($userId);
        $apiHeaders = ['Authorization: Bearer ' . $twitchToken];

        return $this->apiClient->makeCurlCall($apiUrl, $apiHeaders);
    }

    public function getUserFromDatabase($userId)
    {
        return $this->databaseClient->getUserFromDatabase($userId);
    }
}
