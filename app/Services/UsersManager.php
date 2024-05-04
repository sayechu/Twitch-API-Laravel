<?php

namespace App\Services;

class UsersManager
{
    private ApiClient $apiClient;

    public function __construct(ApiClient $apiClient)
    {
        $this->apiClient = $apiClient;
    }

    public function getUserInfoById(string $userId): array
    {
        $api_url = "https://api.twitch.tv/helix/users?id=" . urlencode($userId);

        $responseGetToken = $this->apiClient->getToken();
        $twitchToken = json_decode($responseGetToken, true)['access_token'];

        $api_headers = array(
            'Authorization: Bearer ' . $twitchToken,
        );

        $userInfo = $this->apiClient->makeCurlCall($api_url, $api_headers);

        return json_decode($userInfo, true)['data'];
    }
}
