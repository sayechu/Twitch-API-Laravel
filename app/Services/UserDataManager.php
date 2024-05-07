<?php

namespace App\Services;

class UserDataManager
{
    private TokenProvider $tokenProvider;
    private UserDataProvider $userDataProvider;

    public function __construct(TokenProvider $tokenProvider, UserDataProvider $userDataProvider)
    {
        $this->tokenProvider = $tokenProvider;
        $this->userDataProvider = $userDataProvider;
    }

    public function getUserData(string $userId): array
    {
        $api_url = "https://api.twitch.tv/helix/users?id=" . urlencode($userId);

        $twitchToken = $this->tokenProvider->getToken();

        $api_headers = array('Authorization: Bearer ' . $twitchToken);
        return $this->userDataProvider->getUserData($api_url, $api_headers);
    }
}
