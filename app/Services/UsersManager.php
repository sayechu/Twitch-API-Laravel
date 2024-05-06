<?php

namespace App\Services;

class UsersManager
{
    private ApiClient $apiClient;
    private DatabaseClient $databaseClient;

    public function __construct(ApiClient $apiClient, DatabaseClient $databaseClient)
    {
        $this->apiClient = $apiClient;
        $this->databaseClient = $databaseClient;
    }

    public function getUserInfoById(string $user): array
    {
        $userData = $this->databaseClient->getUserFromDatabase($user);

        if (!$userData) {
            $api_url = "https://api.twitch.tv/helix/users?id=" . urlencode($user);

            $responseGetToken = $this->apiClient->getToken();
            $twitchToken = json_decode($responseGetToken, true)['access_token'];

            $api_headers = array(
                'Authorization: Bearer ' . $twitchToken,
            );

            $userInfo = $this->apiClient->makeCurlCall($api_url, $api_headers);
            $userData =  json_decode($userInfo, true)['data'];

            $this->databaseClient->addUserToDatabase($userData);
        }

        return $userData;
    }
}
